<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AreaAuditoria;
use App\Models\AuditLog;
use App\Models\Coleta;
use App\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColetaImportController extends Controller
{
    public function showForm()
    {
        $lojas = Loja::orderBy('nome')->get();
        return view('admin.import.coletas', compact('lojas'));
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:102400',
            'loja_id' => 'required|exists:lojas,id',
        ]);

        $loja = Loja::findOrFail($validated['loja_id']);

        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $handle = fopen($validated['arquivo']->getPathname(), 'r');
        if (!$handle) {
            return back()->with('error', 'Não foi possível abrir o arquivo.');
        }

        $header = fgetcsv($handle, 0, ',', '"');
        if (!$header || count($header) < 7) {
            fclose($handle);
            return back()->with('error', 'Formato CSV inválido. O arquivo deve ter pelo menos 7 colunas separadas por vírgula.');
        }

        $stats = [
            'total' => 0,
            'importadas' => 0,
            'puladas' => 0,
            'erros' => 0,
            'areas_criadas' => 0,
        ];

        $errosDetalhados = [];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
                $stats['total']++;

                if ($stats['total'] % 500 === 0) {
                    DB::reconnect();
                }

                if (count($row) < 7 || empty(trim($row[4] ?? ''))) {
                    $stats['puladas']++;
                    continue;
                }

                try {
                    $setorNome = trim($row[2] ?? '');
                    $areaAuditoria = null;

                    if (!empty($setorNome)) {
                        $areaAuditoria = AreaAuditoria::whereHas('lojas', function ($q) use ($loja) {
                                $q->where('lojas.id', $loja->id);
                            })
                            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($setorNome)])
                            ->first();

                        if (!$areaAuditoria) {
                            $areaAuditoria = AreaAuditoria::create([
                                'nome' => $setorNome,
                            ]);
                            $areaAuditoria->lojas()->attach($loja->id);
                            $stats['areas_criadas']++;
                        }
                    }

                    $dataValidade = $this->parseDate(trim($row[6] ?? ''));
                    if (!$dataValidade) {
                        $errosDetalhados[] = "Linha {$stats['total']}: data inválida \"{$row[6]}\"";
                        $stats['puladas']++;
                        continue;
                    }

                    $quantidade = (int) trim($row[5] ?? '0');
                    if ($quantidade <= 0) {
                        $errosDetalhados[] = "Linha {$stats['total']}: quantidade inválida \"{$row[5]}\"";
                        $stats['puladas']++;
                        continue;
                    }

                    $ean = trim($row[4] ?? '');
                    $csvDescricao = trim($row[3] ?? '');
                    $barcode = \App\Models\Barcode::where('ean', $ean)->with('product')->first();
                    $descricao = $barcode?->product?->description ?? ($csvDescricao ?: 'Produto não encontrado');

                    $coleta = Coleta::where('loja_id', $loja->id)
                        ->where('area_auditoria_id', $areaAuditoria?->id)
                        ->where('ean', $ean)
                        ->where('data_validade', $dataValidade)
                        ->first();

                    if ($coleta) {
                        $coleta->update([
                            'quantidade' => $quantidade,
                            'descricao' => $descricao,
                        ]);
                        $stats['importadas']++;
                    } else {
                        Coleta::create([
                            'loja_id' => $loja->id,
                            'area_auditoria_id' => $areaAuditoria?->id,
                            'user_id' => auth()->id(),
                            'descricao' => $descricao,
                            'ean' => $ean,
                            'quantidade' => $quantidade,
                            'data_validade' => $dataValidade,
                        ]);
                        $stats['importadas']++;
                    }
                } catch (\Exception $e) {
                    $stats['erros']++;
                    $errosDetalhados[] = "Linha {$stats['total']}: erro interno ({$e->getMessage()})";
                    \Log::warning('Erro ao importar coleta', [
                        'linha' => $stats['total'],
                        'ean' => $row[4] ?? '',
                        'erro' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            if ($stats['importadas'] == 0 && !empty($errosDetalhados)) {
                fclose($handle);
                $msg = "Nenhuma coleta importada. Erros encontrados:<br>";
                $msg .= implode("<br>", array_slice($errosDetalhados, 0, 20));
                if (count($errosDetalhados) > 20) {
                    $msg .= "<br>... e mais " . (count($errosDetalhados) - 20) . " erro(s)";
                }
                return back()->with('error', $msg);
            }

            $mensagem = "Importação concluída! "
                . "Total de linhas: {$stats['total']}, "
                . "Importadas/atualizadas: {$stats['importadas']}, "
                . "Puladas: {$stats['puladas']}, "
                . "Áreas criadas: {$stats['areas_criadas']}";

            if ($stats['erros'] > 0) {
                $mensagem .= ", Erros internos: {$stats['erros']}";
            }

            if (!empty($errosDetalhados)) {
                $mensagem .= "<br><br>Atenção:<br>";
                $mensagem .= implode("<br>", array_slice($errosDetalhados, 0, 20));
                if (count($errosDetalhados) > 20) {
                    $mensagem .= "<br>... e mais " . (count($errosDetalhados) - 20) . " erro(s)";
                }
            }

            $descLog = "Importação concluída. Total: {$stats['total']}, Importadas: {$stats['importadas']}, Puladas: {$stats['puladas']}, Áreas: {$stats['areas_criadas']}";
            if ($stats['erros'] > 0) {
                $descLog .= ", Erros: {$stats['erros']}";
                $primeirosErros = array_slice($errosDetalhados, 0, 10);
                $descLog .= " | " . implode(" | ", $primeirosErros);
            }

            AuditLog::log(
                "Importou coletas: {$validated['arquivo']->getClientOriginalName()}",
                'import',
                null,
                $descLog
            );

            $type = !empty($errosDetalhados) ? 'warning' : 'success';
            fclose($handle);
            return back()->with($type, $mensagem);
        } catch (\Exception $e) {
            try { DB::rollBack(); } catch (\Exception $ignored) {}
            if (is_resource($handle)) {
                fclose($handle);
            }
            return back()->with('error', 'Erro geral na importação: ' . $e->getMessage());
        }
    }

    private function parseDate(string $value): ?string
    {
        $formats = ['d/m/Y', 'Y-m-d', 'd/m/y'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }
        return null;
    }
}
