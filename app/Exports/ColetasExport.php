<?php

namespace App\Exports;

use App\Models\Coleta;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ColetasExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $user;
    protected $lojaId;
    protected $dias;
    protected $dataInicio;
    protected $dataFim;
    protected $userId;
    protected $ean;
    protected $descricao;
    protected $areaAuditoriaId;

    public function __construct(
        $lojaId = null, $dias = null, $dataInicio = null, $dataFim = null,
        $user = null, $userId = null, $ean = null, $descricao = null, $areaAuditoriaId = null
    ) {
        $this->user = $user;
        $this->lojaId = $lojaId;
        $this->dias = $dias;
        $this->dataInicio = $dataInicio;
        $this->dataFim = $dataFim;
        $this->userId = $userId;
        $this->ean = $ean;
        $this->descricao = $descricao;
        $this->areaAuditoriaId = $areaAuditoriaId;
    }

    public function query()
    {
        $query = Coleta::with("loja", "user", "areaAuditoria");

        if ($this->user && $this->user->position !== 'ADMIN') {
            $lojaIds = $this->user->lojasAcessoIds();
            if (!empty($lojaIds)) {
                $query->whereIn("loja_id", $lojaIds);
            }
        }

        if ($this->lojaId) {
            $query->where("loja_id", $this->lojaId);
        }

        if ($this->dias) {
            $dias = (int) $this->dias;
            $query->whereBetween("data_validade", [now()->addDay(), now()->addDays($dias)]);
        }

        if ($this->dataInicio) {
            $query->whereDate("data_validade", ">=", $this->dataInicio);
        }

        if ($this->dataFim) {
            $query->whereDate("data_validade", "<=", $this->dataFim);
        }

        if ($this->userId) {
            $query->where("user_id", $this->userId);
        }

        if ($this->ean) {
            $query->where("ean", "like", "%{$this->ean}%");
        }

        if ($this->descricao) {
            $query->where("descricao", "like", "%{$this->descricao}%");
        }

        if ($this->areaAuditoriaId) {
            $query->where("area_auditoria_id", $this->areaAuditoriaId);
        }

        return $query->orderBy("data_validade");
    }

    public function headings(): array
    {
        return [
            "ID",
            "Loja",
            "Auditor",
            "Setor",
            "Descricao",
            "EAN",
            "Quantidade",
            "Validade",
            "Dias a Vencer",
            "Data/Hora",
        ];
    }

    public function map($coleta): array
    {
        return [
            $coleta->id,
            $coleta->loja->nome ?? "-",
            $coleta->user->name ?? "-",
            $coleta->areaAuditoria->nome ?? "-",
            $coleta->descricao,
            $coleta->ean,
            $coleta->quantidade,
            $coleta->data_validade->format("d/m/Y"),
            $coleta->dias_a_vencer,
            $coleta->datahora ? $coleta->datahora->format("d/m/Y H:i") : "-",
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ["font" => ["bold" => true, "color" => ["rgb" => "FFFFFF"]], "fill" => ["fillType" => "solid", "startColor" => ["rgb" => "005922"]]],
        ];
    }
}
