<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barcode;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    private const CACHE_PREFIX = 'import_progress_';
    private const CHUNK_SIZE = 200;
    private const MAX_ERROR_DETAILS = 100;

    public function showForm()
    {
        return view('admin.import.form');
    }

    public function processFile(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $filePath = $this->resolveFilePath($request);

        if (!$filePath || !file_exists($filePath)) {
            $msg = 'Arquivo CSV não encontrado.';
            return $request->expectsJson()
                ? response()->json(['error' => $msg], 400)
                : back()->with('error', $msg);
        }

        $handle = fopen($filePath, 'r');
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
        $lineNumber = 1;
        $chunk = [];

        while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
            $lineNumber++;

            if (count($row) < 3) {
                $stats['errors']++;
                if (count($errorDetails) < self::MAX_ERROR_DETAILS) {
                    $errorDetails[] = [
                        'line' => $lineNumber,
                        'code' => $row[0] ?? '',
                        'ean' => $row[2] ?? '',
                        'reason' => 'Linha com menos de 3 colunas',
                    ];
                }
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
        set_time_limit(0);

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

        DB::transaction(function () use ($rows, &$chunkStats, &$errorDetails, &$currentLine) {
            foreach ($rows as $row) {
                $currentLine++;

                if (count($row) < 3 || empty(trim($row[0])) || empty(trim($row[2]))) {
                    $chunkStats['errors']++;
                    $this->collectError($errorDetails, $currentLine, $row[0] ?? '', $row[2] ?? '', 'Código ou EAN vazio');
                    continue;
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
                        $chunkStats['created_products']++;
                    } elseif ($product->wasChanged()) {
                        $chunkStats['updated_products']++;
                    }

                    $barcode = Barcode::firstOrNew(['ean' => trim($row[2])]);
                    $barcode->product_id = $product->id;

                    if (!$barcode->exists) {
                        $barcode->save();
                        $chunkStats['created_barcodes']++;
                    } else {
                        if ($barcode->isDirty()) {
                            $barcode->save();
                        }
                        $chunkStats['skipped_barcodes']++;
                    }
                } catch (\Exception $e) {
                    $chunkStats['errors']++;
                    $this->collectError($errorDetails, $currentLine, $row[0] ?? '', $row[2] ?? '', $e->getMessage());
                    \Log::warning('Erro ao importar linha', [
                        'code' => $row[0] ?? '',
                        'ean' => $row[2] ?? '',
                        'erro' => $e->getMessage(),
                    ]);
                }
            }
        });

        $progress['current_line'] = $startLine + count($rows);
        $progress['processed'] += count($rows);
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
            return Storage::path($path);
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

    private function processChunk(array $chunk, array &$stats, array &$errorDetails = null): void
    {
        DB::transaction(function () use ($chunk, &$stats, &$errorDetails) {
            foreach ($chunk as $row) {
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

                    $barcode = Barcode::firstOrNew(['ean' => $row['ean']]);
                    $barcode->product_id = $product->id;

                    if (!$barcode->exists) {
                        $barcode->save();
                        $stats['created_barcodes']++;
                    } else {
                        if ($barcode->isDirty()) {
                            $barcode->save();
                        }
                        $stats['skipped_barcodes']++;
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
