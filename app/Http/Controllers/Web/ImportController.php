<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barcode;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    private const CACHE_PREFIX = 'import_progress_';
    private const CHUNK_SIZE = 30;
    private const BATCH_SIZE = 10;
    private const MAX_ERROR_DETAILS = 100;
    private const MAX_RETRIES = 3;
    private const TIME_LIMIT_SAFETY = 10;

    public function showForm()
    {
        return view('admin.import.form');
    }

    public function processFile(Request $request)
    {
        $filePath = $this->resolveFilePath($request);

        if (!$filePath || !file_exists($filePath)) {
            $msg = 'Arquivo CSV não encontrado.';
            return $request->expectsJson()
                ? response()->json(['error' => $msg], 400)
                : back()->with('error', $msg);
        }

        return $this->processFileSync($filePath, $request);
    }

    private function processFileSync(string $filePath, Request $request)
    {
        set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return back()->with('error', 'Não foi possível abrir o arquivo.');
        }

        $header = fgetcsv($handle, 0, ',', '"');

        if (!$header || count($header) < 3) {
            fclose($handle);
            $msg = 'Formato CSV inválido. Colunas esperadas: COD,DESCRICAO,EAN';
            return $request->expectsJson()
                ? response()->json(['error' => $msg], 400)
                : back()->with('error', $msg);
        }

        $stats = [
            'created_products' => 0,
            'updated_products' => 0,
            'created_barcodes' => 0,
            'skipped_barcodes' => 0,
            'errors' => 0,
            'total_rows' => 0,
        ];

        $errorDetails = [];
        $chunk = [];
        $lineNumber = 1;

        while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
            $lineNumber++;

            if (count($row) < 3) {
                $stats['errors']++;
                $this->collectError($errorDetails, $lineNumber, $row[0] ?? '', $row[2] ?? '', 'Linha com menos de 3 colunas');
                continue;
            }

            $stats['total_rows']++;
            $chunk[] = [
                'code' => trim($row[0]),
                'description' => trim($row[1]),
                'ean' => trim($row[2]),
                'custo' => isset($row[3]) ? trim($row[3]) : 0,
                'line' => $lineNumber,
            ];

            if (count($chunk) >= self::CHUNK_SIZE) {
                $this->keepAlive();
                $this->processChunk($chunk, $stats, $errorDetails);
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            $this->keepAlive();
            $this->processChunk($chunk, $stats, $errorDetails);
        }

        fclose($handle);

        $message = "Importação concluída! "
            . "Produtos criados: {$stats['created_products']}, "
            . "Atualizados: {$stats['updated_products']}, "
            . "Códigos de barras criados: {$stats['created_barcodes']}, "
            . "Pulados (já existem): {$stats['skipped_barcodes']}";

        if ($stats['errors'] > 0) {
            $message .= ", Erros: {$stats['errors']}";
        }

        AuditLog::log('import', 'csv', 0, "Importou CSV: {$message}");

        return back()
            ->with('success', $message)
            ->with('import_errors', $errorDetails);
    }

    public function start(Request $request)
    {
        $filePath = $this->resolveFilePath($request);

        if (!$filePath || !file_exists($filePath)) {
            return response()->json(['error' => 'Arquivo CSV não encontrado.'], 400);
        }

        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        $file = null;

        $total = max(0, $totalLines - 1);

        $this->clearProgress();

        Cache::put($this->cacheKey(), [
            'file_path' => $filePath,
            'status' => 'processing',
            'total' => $total,
            'processed' => 0,
            'current_line' => 1,
            'created_products' => 0,
            'updated_products' => 0,
            'created_barcodes' => 0,
            'skipped_barcodes' => 0,
            'errors' => 0,
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
            Log::error('Import chunk exception', [
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
                Log::error('Import PHP Fatal Error', $error);
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
            'created_products' => 0,
            'updated_products' => 0,
            'created_barcodes' => 0,
            'skipped_barcodes' => 0,
            'errors' => 0,
        ];

        $errorDetails = &$progress['error_details'];
        if (!isset($errorDetails)) {
            $errorDetails = [];
        }

        $currentLine = $startLine;

        $this->processRowsInBatches($rows, $chunkStats, $errorDetails, $currentLine, $startTime, $maxTime);

        $processedCount = $currentLine - $startLine;
        $progress['current_line'] = $startLine + $processedCount;
        $progress['processed'] += $processedCount;
        $progress['created_products'] += $chunkStats['created_products'];
        $progress['updated_products'] += $chunkStats['updated_products'];
        $progress['created_barcodes'] += $chunkStats['created_barcodes'];
        $progress['skipped_barcodes'] += $chunkStats['skipped_barcodes'];
        $progress['errors'] += $chunkStats['errors'];

        $this->keepAlive();

        $done = $progress['processed'] >= $progress['total'] || $eof;

        if ($done) {
            $progress['status'] = 'complete';
            $progress['message'] = $this->buildMessage($progress);
            AuditLog::log('import', 'csv', 0, "Importou CSV: {$progress['message']}");
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

    private function processRowsInBatches(array $rows, array &$stats, array &$errorDetails, int &$currentLine, int $startTime = 0, int $maxTime = 0): void
    {
        foreach (array_chunk($rows, self::BATCH_SIZE) as $batch) {
            if ($maxTime && (time() - $startTime) > ($maxTime - self::TIME_LIMIT_SAFETY)) {
                break;
            }
            $this->processBatchWithRetry($batch, $stats, $errorDetails, $currentLine);
        }
    }

    private function processBatchWithRetry(array $batch, array &$stats, array &$errorDetails, int &$currentLine): void
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts < self::MAX_RETRIES) {
            $attempts++;
            try {
                DB::transaction(function () use ($batch, &$stats, &$errorDetails, &$currentLine) {
                    foreach ($batch as $row) {
                        $currentLine++;
                        $this->processRow($row, $stats, $errorDetails, $currentLine);
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
                $stats['errors']++;
                $currentLine++;
                $this->collectError(
                    $errorDetails,
                    $currentLine,
                    $row[0] ?? '',
                    $row[2] ?? '',
                    'Falha após ' . self::MAX_RETRIES . ' tentativas: ' . $lastException->getMessage()
                );
            }
        }
    }

    private function processRow(array $row, array &$stats, array &$errorDetails, int $currentLine): void
    {
        if (count($row) < 3 || empty(trim($row[0] ?? '')) || empty(trim($row[2] ?? ''))) {
            $stats['errors']++;
            $this->collectError($errorDetails, $currentLine, $row[0] ?? '', $row[2] ?? '', 'Código ou EAN vazio');
            return;
        }

        try {
            $product = Product::updateOrCreate(
                ['code' => trim($row[0])],
                [
                    'description' => trim($row[1]),
                    'custo' => isset($row[3]) ? trim($row[3]) : 0,
                ]
            );

            if ($product->wasRecentlyCreated) {
                $stats['created_products']++;
            } elseif ($product->wasChanged()) {
                $stats['updated_products']++;
            }

            $ean = trim($row[2]);

            $existing = Barcode::where('ean', $ean)->first();

            if ($existing) {
                if ($existing->product_id !== $product->id) {
                    $existing->update(['product_id' => $product->id]);
                }
                $stats['skipped_barcodes']++;
            } else {
                Barcode::create([
                    'ean' => $ean,
                    'product_id' => $product->id,
                ]);
                $stats['created_barcodes']++;
            }
        } catch (\Exception $e) {
            $stats['errors']++;
            $this->collectError($errorDetails, $currentLine, $row[0] ?? '', $row[2] ?? '', $e->getMessage());
            \Log::warning('Erro ao importar linha', [
                'code' => $row[0] ?? '',
                'ean' => $row[2] ?? '',
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

    private function cacheKey(): string
    {
        return self::CACHE_PREFIX . session()->getId();
    }

    private function clearProgress(): void
    {
        Cache::forget($this->cacheKey());
    }

    private function resolveFilePath(Request $request): ?string
    {
        if ($request->hasFile('csv_file')) {
            $path = $request->file('csv_file')->store('imports');
            return \Illuminate\Support\Facades\Storage::path($path);
        }

        $default = base_path('VALIDADE.csv');
        return file_exists($default) ? $default : null;
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
            'created_products' => $progress['created_products'] ?? 0,
            'updated_products' => $progress['updated_products'] ?? 0,
            'created_barcodes' => $progress['created_barcodes'] ?? 0,
            'skipped_barcodes' => $progress['skipped_barcodes'] ?? 0,
            'errors' => $progress['errors'] ?? 0,
            'error_details' => $progress['error_details'] ?? [],
        ];
    }

    private function buildMessage(array $stats): string
    {
        $msg = "Importação concluída! "
            . "Produtos criados: {$stats['created_products']}, "
            . "Atualizados: {$stats['updated_products']}, "
            . "Códigos de barras criados: {$stats['created_barcodes']}, "
            . "Pulados (já existem): {$stats['skipped_barcodes']}";

        if ($stats['errors'] > 0) {
            $msg .= ", Erros: {$stats['errors']}";
        }

        return $msg;
    }

    private function processChunk(array $chunk, array &$stats, array &$errorDetails): void
    {
        foreach (array_chunk($chunk, self::BATCH_SIZE) as $batch) {
            try {
                DB::transaction(function () use ($batch, &$stats, &$errorDetails) {
                    foreach ($batch as $row) {
                        if (empty($row['code']) || empty($row['ean'])) {
                            $stats['errors']++;
                            $this->collectError($errorDetails, $row['line'] ?? 0, $row['code'] ?? '', $row['ean'] ?? '', 'Código ou EAN vazio');
                            continue;
                        }

                        try {
                            $product = Product::updateOrCreate(
                                ['code' => $row['code']],
                                [
                                    'description' => $row['description'],
                                    'custo' => $row['custo'] ?? 0,
                                ]
                            );

                            if ($product->wasRecentlyCreated) {
                                $stats['created_products']++;
                            } elseif ($product->wasChanged()) {
                                $stats['updated_products']++;
                            }

                            $existing = Barcode::where('ean', $row['ean'])->first();

                            if ($existing) {
                                if ($existing->product_id !== $product->id) {
                                    $existing->update(['product_id' => $product->id]);
                                }
                                $stats['skipped_barcodes']++;
                            } else {
                                Barcode::create([
                                    'ean' => $row['ean'],
                                    'product_id' => $product->id,
                                ]);
                                $stats['created_barcodes']++;
                            }
                        } catch (\Exception $e) {
                            $stats['errors']++;
                            $this->collectError($errorDetails, $row['line'] ?? 0, $row['code'] ?? '', $row['ean'] ?? '', $e->getMessage());
                            \Log::warning('Erro ao importar linha', [
                                'code' => $row['code'],
                                'ean' => $row['ean'],
                                'erro' => $e->getMessage(),
                            ]);
                        }
                    }
                });
            } catch (\Exception $e) {
                foreach ($batch as $row) {
                    $stats['errors']++;
                    $this->collectError($errorDetails, $row['line'] ?? 0, $row['code'] ?? '', $row['ean'] ?? '', 'Erro no batch: ' . $e->getMessage());
                }
            }
        }
    }

    private function collectError(?array &$errorDetails, int $line, string $code, string $ean, string $reason): void
    {
        if ($errorDetails !== null && count($errorDetails) < self::MAX_ERROR_DETAILS) {
            $errorDetails[] = [
                'line' => $line,
                'code' => $code,
                'ean' => $ean,
                'reason' => $reason,
            ];
        }
    }

    private function keepAlive(): void
    {
        try {
            DB::connection()->getPdo()->query('SELECT 1');
        } catch (\Exception $e) {
            DB::reconnect();
        }
    }
}
