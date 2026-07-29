<?php

namespace App\Jobs;

use App\Models\AreaAuditoria;
use App\Models\AuditLog;
use App\Models\Coleta;
use App\Models\Loja;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportColetasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    protected string $filePath;
    protected int $lojaId;
    protected int $userId;

    public function __construct(string $filePath, int $lojaId, int $userId)
    {
        $this->filePath = $filePath;
        $this->lojaId = $lojaId;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $loja = Loja::findOrFail($this->lojaId);

        $handle = fopen($this->filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Não foi possível abrir o arquivo: {$this->filePath}");
        }

        fgetcsv($handle, 0, ',', '"');

        $stats = [
            'total' => 0,
            'importadas' => 0,
            'puladas' => 0,
            'erros' => 0,
            'areas_criadas' => 0,
        ];

        $areaCache = [];
        $batch = [];
        $batchSize = 50;

        while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
            $stats['total']++;
            $batch[] = $row;

            if (count($batch) >= $batchSize) {
                $this->processBatch($batch, $loja, $stats, $areaCache);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->processBatch($batch, $loja, $stats, $areaCache);
        }

        fclose($handle);

        $descLog = "Importação concluída (job). Total: {$stats['total']}, "
            . "Importadas: {$stats['importadas']}, "
            . "Puladas: {$stats['puladas']}, "
            . "Áreas: {$stats['areas_criadas']}";
        if ($stats['erros'] > 0) {
            $descLog .= ", Erros: {$stats['erros']}";
        }

        AuditLog::log(
            "Importou coletas: {$this->filePath}",
            'import',
            0,
            $descLog,
            $this->userId
        );
    }

    private function processBatch(array $batch, Loja $loja, array &$stats, array &$areaCache): void
    {
        try {
            DB::transaction(function () use ($batch, $loja, &$stats, &$areaCache) {
                foreach ($batch as $row) {
                    $this->processRow($row, $loja, $stats, $areaCache);
                }
            });
        } catch (\Exception $e) {
            $stats['erros'] += count($batch);
            \Log::error('Falha no batch de importação coletas: ' . $e->getMessage());
        }
    }

    private function processRow(array $row, Loja $loja, array &$stats, array &$areaCache): void
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
                return;
            }

            $quantidade = (int) trim($row[5] ?? '0');
            if ($quantidade <= 0) {
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
                    'user_id' => $this->userId,
                    'ean' => $ean,
                    'descricao' => $descricao ?: null,
                    'quantidade' => $quantidade,
                    'data_validade' => $dataValidade,
                ]);
            }

            $stats['importadas']++;
        } catch (\Exception $e) {
            $stats['erros']++;
            \Log::warning('Erro job import coleta', [
                'ean' => $row[4] ?? '',
                'erro' => $e->getMessage(),
            ]);
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
