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
use Illuminate\Support\Facades\Log;

class ColetaImportController extends Controller
{
    private const CACHE_PREFIX = 'coleta_import_progress_';
    private const CHUNK_SIZE = 100;
    private const BATCH_SIZE = 25;
    private const MAX_ERROR_DETAILS = 100;
    private const MAX_RETRIES = 3;
    private const TIME_LIMIT_SAFETY = 5;

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
        $filePath = \Illuminate\Support\Facades\Storage::path($path);

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
            'error_details' => [],
        ], 7200);

        return response()->json(['total' => $total]);
    }

    public function chunk()
    {
        $this->registerFatalErrorHandler();

        $progress = Cache::get($this->cacheKey());

        if (!$progress) {
            return response()->json(['error' => 'Importação não iniciada.'], 400);
        }

        if ($progress['status'] !== 'processing') {
            return response()->json(['done' => true, 'progress' => $this->buildProgressResponse($progress)]);
        }

        $hasPartialProgress = false;
        $processedBefore = $progress['processed'] ?? 0;

        try {
            $result = $this->processNextChunk($progress);
            $hasPartialProgress = ($progress['processed'] ?? 0) > $processedBefore;
        } catch (\Exception $e) {
            Log::error('Import coleta chunk exception', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'processed' => $progress['processed'] ?? 0,
                'total' => $progress['total'] ?? 0,
                'current_line' => $progress['current_line'] ?? 0,
            ]);

            if ($hasPartialProgress) {
                Cache::put($this->cacheKey(), $progress, 7200);
                return response()->json([
                    'done' => false,
                    'progress' => $this->buildProgressResponse($progress),
                    'warning' => 'Lote parcialmente processado. Continuando...',
                ]);
            }

            return response()->json([
                'error' => 'Erro ao processar lote: ' . $e->getMessage(),
                'progress' => $this->buildProgressResponse($progress),
            ], 500);
        }

        return response()->json($result);
    }

    private function registerFatalErrorHandler(): void
    {
        $cacheKey = $this->cacheKey();
        register_shutdown_function(function () use ($cacheKey) {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                Log::error('Import coleta PHP Fatal Error', $error);
                $progress = Cache::get($cacheKey);
                if ($progress) {
                    $progress['status'] = 'error';
                    $progress['message'] = 'Erro fatal: ' . $error['message'];
                    Cache::put($cacheKey, $progress, 7200);
                }
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => 'Erro fatal do PHP: ' . $error['message'],
                    'progress' => $progress ? $this->buildProgressResponse($progress) : null,
                ]);
            }
        });
    }

    private function processNextChunk(array &$progress): array
    {
        $startTime = time();
        $maxTime = $this->getMaxExecutionTime();

        $file = new \SplFileObject($progress['file_path'], 'r');
        $file->seek($progress['current_line']);

        $rows = [];
        $lineCount = 0;
        $startLine = $progress['current_line'];

        while ($lineCount < self::CHUNK_SIZE && !$file->eof()) {
            $row = $file->fgetcsv(',', '"');
            if ($row !== false && !(count($row) === 1 && $row[0] === null)) {
                $rows[] = $row;
                $lineCount++;
            } else {
                $file->next();
            }

            if ($maxTime && (time() - $startTime) > ($maxTime - self::TIME_LIMIT_SAFETY)) {
                break;
            }
        }

        $eof = $file->eof() || empty($rows);
        $file = null;

        $this->keepAlive();

        $chunkStats = [
            'importadas' => 0,
            'puladas' => 0,
            'areas_criadas' => 0,
            'erros' => 0,
        ];

        $loja = Loja::find($progress['loja_id']);
        $errorDetails = &$progress['error_details'];
        if (!isset($errorDetails)) {
            $errorDetails = [];
        }

        $currentLine = $startLine;

        $this->processRowsInBatches($rows, $loja, $chunkStats, $errorDetails, $currentLine, $startTime, $maxTime);

        $processedCount = $currentLine - $startLine;
        $progress['current_line'] = $startLine + $processedCount;
        $progress['processed'] += $processedCount;
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

        Cache::put($this->cacheKey(), $progress, 7200);

        return [
            'done' => $done,
            'progress' => $this->buildProgressResponse($progress),
        ];
    }

    private function getMaxExecutionTime(): int
    {
        $iniLimit = ini_get('max_execution_time');
        if ($iniLimit === false || $iniLimit === '0') {
            return 0;
        }
        $limit = (int) $iniLimit;
        return $limit > 0 ? $limit : 0;
    }

    private function processRowsInBatches(array $rows, Loja $loja, array &$stats, array &$errorDetails, int &$currentLine, int $startTime = 0, int $maxTime = 0): void
    {
        $areaCache = [];

        foreach (array_chunk($rows, self::BATCH_SIZE) as $batch) {
            if ($maxTime && (time() - $startTime) > ($maxTime - self::TIME_LIMIT_SAFETY)) {
                break;
            }
            $this->processBatchWithRetry($batch, $loja, $stats, $errorDetails, $currentLine, $areaCache);
        }
    }

    private function processBatchWithRetry(array $batch, Loja $loja, array &$stats, array &$errorDetails, int &$currentLine, array &$areaCache): void
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts < self::MAX_RETRIES) {
            $attempts++;
            try {
                DB::transaction(function () use ($batch, $loja, &$stats, &$errorDetails, &$currentLine, &$areaCache) {
                    foreach ($batch as $row) {
                        $currentLine++;
                        $this->processRow($row, $loja, $stats, $errorDetails, $currentLine, $areaCache);
                    }
                });
                $lastException = null;
                break;
            } catch (\Exception $e) {
                $lastException = $e;
                $currentLine -= count($batch);
                if ($attempts < self::MAX_RETRIES) {
                    usleep(500000 * $attempts);
                    $this->keepAlive();
                    DB::reconnect();
                }
            }
        }

        if ($lastException) {
            foreach ($batch as $row) {
                $stats['erros']++;
                $currentLine++;
                $this->collectError(
                    $errorDetails,
                    $currentLine,
                    $row[4] ?? '',
                    'Falha após ' . self::MAX_RETRIES . ' tentativas: ' . $lastException->getMessage()
                );
            }
        }
    }

    private function processRow(array $row, Loja $loja, array &$stats, array &$errorDetails, int $currentLine, array &$areaCache): void
    {
        if (count($row) < 7 || empty(trim($row[4] ?? ''))) {
            $stats['puladas']++;
            return;
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
                    ->where('nome', $setorNome)
                    ->first();

                    if (!$areaAuditoria) {
                        $areaAuditoria = AreaAuditoria::create(['nome' => $setorNome]);
                        $areaAuditoria->lojas()->attach($loja->id);
                        $stats['areas_criadas']++;
                    }

                    $areaCache[$setorNome] = $areaAuditoria;
                }
            }

            $dataValidade = $this->parseDate(trim($row[6] ?? ''));
            if (!$dataValidade) {
                $stats['puladas']++;
                $this->collectError($errorDetails, $currentLine, $row[4] ?? '', "Data inválida: {$row[6]}");
                return;
            }

            $quantidade = (int) trim($row[5] ?? '0');
            if ($quantidade <= 0) {
                $stats['puladas']++;
                $this->collectError($errorDetails, $currentLine, $row[4] ?? '', "Quantidade inválida: {$row[5]}");
                return;
            }

            $ean = trim($row[4] ?? '');
            $descricao = trim($row[3] ?? '');

            $coleta = Coleta::where('loja_id', $loja->id)
                ->where('area_auditoria_id', $areaAuditoria?->id)
                ->where('ean', $ean)
                ->where('data_validade', $dataValidade)
                ->first();

            if ($coleta) {
                $coleta->update([
                    'quantidade' => $quantidade,
                    'descricao' => $descricao ?: $coleta->descricao,
                ]);
            } else {
                Coleta::create([
                    'loja_id' => $loja->id,
                    'area_auditoria_id' => $areaAuditoria?->id,
                    'user_id' => auth()->id(),
                    'ean' => $ean,
                    'descricao' => $descricao ?: null,
                    'quantidade' => $quantidade,
                    'data_validade' => $dataValidade,
                ]);
            }

            $stats['importadas']++;
        } catch (\Exception $e) {
            $stats['erros']++;
            $this->collectError($errorDetails, $currentLine, $row[4] ?? '', $e->getMessage());
            \Log::warning('Erro ao importar coleta', [
                'linha' => $currentLine,
                'ean' => $row[4] ?? '',
                'erro' => $e->getMessage(),
            ]);
        }
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
        @ini_set('memory_limit', '512M');

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
        $areaCache = [];

        try {
            $batch = [];
            $batchCount = 0;

            while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
                $stats['total']++;
                $batch[] = $row;
                $batchCount++;

                if ($batchCount >= self::BATCH_SIZE) {
                    $this->processImportBatch($batch, $loja, $stats, $errosDetalhados, $areaCache);
                    $batch = [];
                    $batchCount = 0;
                }
            }

            if (!empty($batch)) {
                $this->processImportBatch($batch, $loja, $stats, $errosDetalhados, $areaCache);
            }

            fclose($handle);

            if ($stats['importadas'] == 0 && !empty($errosDetalhados)) {
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
            return back()->with($type, $mensagem);
        } catch (\Exception $e) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            return back()->with('error', 'Erro geral na importação: ' . $e->getMessage());
        }
    }

    private function processImportBatch(array $batch, Loja $loja, array &$stats, array &$errosDetalhados, array &$areaCache): void
    {
        $this->keepAlive();

        try {
            DB::transaction(function () use ($batch, $loja, &$stats, &$errosDetalhados, &$areaCache) {
                foreach ($batch as $row) {
                    $this->importSingleRow($row, $loja, $stats, $errosDetalhados, $areaCache);
                }
            });
        } catch (\Exception $e) {
            $stats['erros'] += count($batch);
            $errosDetalhados[] = "Batch com " . count($batch) . " linhas: " . $e->getMessage();
        }
    }

    private function importSingleRow(array $row, Loja $loja, array &$stats, array &$errosDetalhados, array &$areaCache): void
    {
        if (count($row) < 7 || empty(trim($row[4] ?? ''))) {
            $stats['puladas']++;
            return;
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
                    ->where('nome', $setorNome)
                    ->first();

                    if (!$areaAuditoria) {
                        $areaAuditoria = AreaAuditoria::create(['nome' => $setorNome]);
                        $areaAuditoria->lojas()->attach($loja->id);
                        $stats['areas_criadas']++;
                    }

                    $areaCache[$setorNome] = $areaAuditoria;
                }
            }

            $dataValidade = $this->parseDate(trim($row[6] ?? ''));
            if (!$dataValidade) {
                $errosDetalhados[] = "Linha {$stats['total']}: data inválida \"{$row[6]}\"";
                $stats['puladas']++;
                return;
            }

            $quantidade = (int) trim($row[5] ?? '0');
            if ($quantidade <= 0) {
                $errosDetalhados[] = "Linha {$stats['total']}: quantidade inválida \"{$row[5]}\"";
                $stats['puladas']++;
                return;
            }

            $ean = trim($row[4] ?? '');
            $descricao = trim($row[3] ?? '');

            $coleta = Coleta::where('loja_id', $loja->id)
                ->where('area_auditoria_id', $areaAuditoria?->id)
                ->where('ean', $ean)
                ->where('data_validade', $dataValidade)
                ->first();

            if ($coleta) {
                $coleta->update([
                    'quantidade' => $quantidade,
                    'descricao' => $descricao ?: $coleta->descricao,
                ]);
            } else {
                Coleta::create([
                    'loja_id' => $loja->id,
                    'area_auditoria_id' => $areaAuditoria?->id,
                    'user_id' => auth()->id(),
                    'ean' => $ean,
                    'descricao' => $descricao ?: null,
                    'quantidade' => $quantidade,
                    'data_validade' => $dataValidade,
                ]);
            }

            $stats['importadas']++;
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
            'error_details' => $progress['error_details'] ?? [],
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

    private function collectError(?array &$errorDetails, int $line, string $ean, string $reason): void
    {
        if ($errorDetails !== null && count($errorDetails) < self::MAX_ERROR_DETAILS) {
            $errorDetails[] = [
                'line' => $line,
                'ean' => $ean,
                'reason' => $reason,
            ];
        }
    }
}
