<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Barcode;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportProdutosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    protected string $filePath;
    protected int $userId;

    public function __construct(string $filePath, int $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $handle = fopen($this->filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Não foi possível abrir o arquivo: {$this->filePath}");
        }

        fgetcsv($handle, 0, ',', '"');

        $stats = [
            'created_products' => 0,
            'updated_products' => 0,
            'created_barcodes' => 0,
            'skipped_barcodes' => 0,
            'errors' => 0,
            'total_rows' => 0,
        ];

        $batch = [];
        $batchSize = 50;

        while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
            if (count($row) < 3) {
                $stats['errors']++;
                continue;
            }

            $stats['total_rows']++;
            $batch[] = [
                'code' => trim($row[0]),
                'description' => trim($row[1]),
                'ean' => trim($row[2]),
                'custo' => isset($row[3]) ? trim($row[3]) : 0,
            ];

            if (count($batch) >= $batchSize) {
                $this->processBatch($batch, $stats);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->processBatch($batch, $stats);
        }

        fclose($handle);

        $message = "Importação concluída! "
            . "Produtos criados: {$stats['created_products']}, "
            . "Atualizados: {$stats['updated_products']}, "
            . "Códigos de barras criados: {$stats['created_barcodes']}, "
            . "Pulados: {$stats['skipped_barcodes']}";

        if ($stats['errors'] > 0) {
            $message .= ", Erros: {$stats['errors']}";
        }

        AuditLog::log('import', 'csv', $this->userId, "Importou CSV (job): {$message}");
    }

    private function processBatch(array $batch, array &$stats): void
    {
        try {
            DB::transaction(function () use ($batch, &$stats) {
                foreach ($batch as $row) {
                    try {
                        if (empty($row['code']) || empty($row['ean'])) {
                            $stats['errors']++;
                            continue;
                        }

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
                        \Log::warning('Erro job import produto', [
                            'code' => $row['code'] ?? '',
                            'ean' => $row['ean'] ?? '',
                            'erro' => $e->getMessage(),
                        ]);
                    }
                }
            });
        } catch (\Exception $e) {
            $stats['errors'] += count($batch);
            \Log::error('Falha no batch de importação: ' . $e->getMessage());
        }
    }
}
