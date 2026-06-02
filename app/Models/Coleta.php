<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coleta extends Model
{
    protected $fillable = [
        "loja_id", "area_auditoria_id", "user_id", "descricao",
        "ean", "quantidade", "data_validade", "datahora"
    ];

    protected $casts = [
        "data_validade" => "date",
        "datahora" => "datetime",
        "quantidade" => "integer",
    ];

    public function loja()
    {
        return $this->belongsTo(Loja::class);
    }

    public function areaAuditoria()
    {
        return $this->belongsTo(AreaAuditoria::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDiasAVencerAttribute()
    {
        return now()->diffInDays($this->data_validade, false);
    }
}
