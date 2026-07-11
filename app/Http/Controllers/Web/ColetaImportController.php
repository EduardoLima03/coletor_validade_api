<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AreaAuditoria;
use App\Models\AuditLog;
use App\Models\Coleta;
use App\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ColetaImportController extends Controller
{
    private const CACHE_PREFIX = 'coleta_import_progress_';
    private const CHUNK_SIZE = 200;

    public function showForm()
    {
        $lojas = Loja::orderBy('nome')->get();
        return view('admin.import.coletas', compact('lojas'));
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:102400',
            'loja_id' => 'required|exists:lojas,id',
        ]);

        set_time_limit(0);

        $path = $request->file('csv_file')->store('imports');
        $filePath = Storage::path($path);

        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        $file = null;

        $total = max(0, $totalLines - 1);

        $this->clearProgress();

        Cache::put($this->cacheKey(), [
            'file_path' => $filePath,
            'loja_id' => $validated['loja_id'],
            'status' => 'processing',
            'total' => $total,
            'processed' => 0,
            'current_line' => 1,
            'importadas' => 0,
            'puladas' => 0,
            'areas_criadas' => 0,
            'erros' => 0,
            'message' => null,
        ], 3600);

        return response()->json(['total' => $total]);
    }

    public function chunk()
    {
        set_time_limit(0);

        $progress = Cache::get($this->cacheKey());

        if (!$progress) {
            return response()->json(['error' => 'Importação não iniciada.'], 400);
        }

        if ($progress['status'] !== 'processing') {
            return response()->json(['done' => true, 'progress' => $this->buildProgressResponse($progress)]);
        }

        $handle = fopen($progress['file_path'], 'r');

        for ($i = 0; $i < $progress['current_line']; $i++) {
            fgets($handle);
        }

        $rows = [];
        $lineCount = 0;
        $startLine = $progress['current_line'];

        while ($lineCount < self::CHUNK_SIZE && ($row = fgetcsv($handle, 0, ',', '"')) !== false) {
            $rows[] = $row;
            $lineCount++;
        }

        $eof = feof($handle);
        fclose($handle);

        $this->keepAlive();

        $chunkStats = [
            'importadas' => 0,
            'puladas' => 0,
            'areas_criadas' => 0,
            'erros' => 0,
        ];

        $loja = Loja::find($progress['loja_id']);
        $areaCache = [];

        DB::transaction(function () use ($rows, $loja, &$chunkStats, &$areaCache) {
            $this->keepAlive();
            foreach ($rows as $row) {
                if (count($row) < 7 || empty(trim($row[4] ?? ''))) {
                    $chunkStats['puladas']++;
                    continue;
                }

                try {
                    $setorNome = trim($row[2] ?? '');
                    $areaAuditoria = null;

                    if (!empty($setorNome)) {
                        if (isset($areaCache[$setorNome])) {
                            $areaAuditoria = $areaCache[$setorNome];
                        } else {
                            $areaAuditoria = AreaAuditoria::whereHas('lojas', function ($q) use ($loja) {
                                $q->where('lojas.id', $loja->id);
                            })
                            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($setorNome)])
                            ->first();

                            if (!$areaAuditoria) {
                                $areaAuditoria = AreaAuditoria::create(['nome' => $setorNome]);
                                $areaAuditoria->lojas()->attach($loja->id);
                                $chunkStats['areas_criadas']++;
                            }

                            $areaCache[$setorNome] = $areaAuditoria;
                        }
                    }

                    $dataValidade = $this->parseDate(trim($row[6] ?? ''));
                    if (!$dataValidade) {
                        $chunkStats['puladas']++;
                        continue;
                    }

                    $quantidade = (int) trim($row[5] ?? '0');
                    if ($quantidade <= 0) {
                        $chunkStats['puladas']++;
                        continue;
                    }

                    $ean = trim($row[4] ?? '');

                    $coleta = Coleta::where('loja_id', $loja->id)
                        ->where('area_auditoria_id', $areaAuditoria?->id)
                        ->where('ean', $ean)
                        ->where('data_validade', $dataValidade)
                        ->first();

                    if ($coleta) {
                        $coleta->update([
                            'quantidade' => $quantidade,
                        ]);
                    } else {
                        Coleta::create([
                            'loja_id' => $loja->id,
                            'area_auditoria_id' => $areaAuditoria?->id,
                            'user_id' => auth()->id(),
                            'ean' => $ean,
                            'quantidade' => $quantidade,
                            'data_validade' => $dataValidade,
                        ]);
                    }

                    $chunkStats['importadas']++;
                } catch (\Exception $e) {
                    $chunkStats['erros']++;
                    \Log::warning('Erro ao importar coleta', [
                        'ean' => $row[4] ?? '',
                        'erro' => $e->getMessage(),
                    ]);
                }
            }
        });

        $progress['current_line'] = $startLine + count($rows);
        $progress['processed'] += count($rows);
        $progress['importadas'] += $chunkStats['importadas'];
        $progress['puladas'] += $chunkStats['puladas'];
        $progress['areas_criadas'] += $chunkStats['areas_criadas'];
        $progress['erros'] += $chunkStats['erros'];

        $this->keepAlive();

        $done = $progress['processed'] >= $progress['total'] || $eof;

        if ($done) {
            $progress['status'] = 'complete';
            $progress['message'] = $this->buildMessage($progress);
            AuditLog::log(
                "Importou coletas: {$progress['file_path']}",
                'import',
                null,
                $progress['message']
            );
        }

        Cache::put($this->cacheKey(), $progress, 3600);

        return response()->json([
            'done' => $done,
            'progress' => $this->buildProgressResponse($progress),
        ]);
    }

    public function progress()
    {
        $progress = Cache::get($this->cacheKey());

        if (!$progress) {
            return response()->json(['status' => 'idle']);
        }

        return response()->json($this->buildProgressResponse($progress));
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

        try {
            while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
                $stats['total']++;

                $this->keepAlive();

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

                    if ($stats['total'] % 500 === 0) {
                        DB::reconnect();
                    }

                    $coleta = Coleta::where('loja_id', $loja->id)
                        ->where('area_auditoria_id', $areaAuditoria?->id)
                        ->where('ean', $ean)
                        ->where('data_validade', $dataValidade)
                        ->first();

                    if ($coleta) {
                        $coleta->update([
                            'quantidade' => $quantidade,
                        ]);
                        $stats['importadas']++;
                    } else {
                        Coleta::create([
                            'loja_id' => $loja->id,
                            'area_auditoria_id' => $areaAuditoria?->id,
                            'user_id' => auth()->id(),
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
            if (is_resource($handle)) {
                fclose($handle);
            }
            return back()->with('error', 'Erro geral na importação: ' . $e->getMessage());
        }
    }

    private function cacheKey(): string
    {
        return self::CACHE_PREFIX . session()->getId();
    }

    private function clearProgress(): void
    {
        Cache::forget($this->cacheKey());
    }

    private function buildProgressResponse(array $progress): array
    {
        $percent = $progress['total'] > 0
            ? round(($progress['processed'] / $progress['total']) * 100, 1)
            : 100;

        return [
            'status' => $progress['status'] ?? 'idle',
            'total' => $progress['total'] ?? 0,
            'processed' => $progress['processed'] ?? 0,
            'percent' => $percent,
            'message' => $progress['message'] ?? null,
            'importadas' => $progress['importadas'] ?? 0,
            'puladas' => $progress['puladas'] ?? 0,
            'areas_criadas' => $progress['areas_criadas'] ?? 0,
            'erros' => $progress['erros'] ?? 0,
        ];
    }

    private function buildMessage(array $stats): string
    {
        $msg = "Importação concluída! "
            . "Importadas/atualizadas: {$stats['importadas']}, "
            . "Puladas: {$stats['puladas']}, "
            . "Áreas criadas: {$stats['areas_criadas']}";

        if ($stats['erros'] > 0) {
            $msg .= ", Erros internos: {$stats['erros']}";
        }

        return $msg;
    }

    private function keepAlive(): void
    {
        try {
            DB::connection()->getPdo()->query('SELECT 1');
        } catch (\Exception $e) {
            DB::reconnect();
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
