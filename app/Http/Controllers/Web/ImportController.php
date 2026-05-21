<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barcode;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function showForm()
    {
        return view('admin.import.form');
    }

    public function processFile(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $filePath = base_path('VALIDADE.csv');

        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            $filePath = $file->getRealPath();
        }

        if (!file_exists($filePath)) {
            return back()->with('error', 'Arquivo CSV não encontrado.');
        }

        $stats = [
            'created_products' => 0,
            'updated_products' => 0,
            'created_barcodes' => 0,
            'skipped_barcodes' => 0,
            'errors' => 0,
            'total_rows' => 0,
        ];

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle, 0, ',', '"');

        if (!$header || count($header) < 3) {
            fclose($handle);
            return back()->with('error', 'Formato CSV inválido. Colunas esperadas: COD,DESCRICAO,EAN');
        }

        $chunk = [];
        $chunkSize = 500;

        while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
            if (count($row) < 3) {
                $stats['errors']++;
                continue;
            }

            $stats['total_rows']++;
            $chunk[] = [
                'code' => trim($row[0]),
                'description' => trim($row[1]),
                'ean' => trim($row[2]),
            ];

            if (count($chunk) >= $chunkSize) {
                $this->processChunk($chunk, $stats);
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            $this->processChunk($chunk, $stats);
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

        return back()->with('success', $message);
    }

    private function processChunk(array $chunk, array &$stats): void
    {
        DB::transaction(function () use ($chunk, &$stats) {
            foreach ($chunk as $row) {
                if (empty($row['code']) || empty($row['ean'])) {
                    $stats['errors']++;
                    continue;
                }

                try {
                    $product = Product::updateOrCreate(
                        ['code' => $row['code']],
                        ['description' => $row['description']]
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
                    \Log::warning('Erro ao importar linha', [
                        'code' => $row['code'],
                        'ean' => $row['ean'],
                        'erro' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}
