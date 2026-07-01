<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coleta extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "loja_id", "area_auditoria_id", "user_id", "descricao",
        "ean", "quantidade", "unidade", "data_validade", "datahora"
    ];

    protected $casts = [
        "data_validade" => "date",
        "datahora" => "datetime",
        "deleted_at" => "datetime",
    ];

    protected $appends = ["product_name"];

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

    public function barcode()
    {
        return $this->belongsTo(Barcode::class, "ean", "ean");
    }

    public function getProductNameAttribute(): ?string
    {
        return $this->barcode?->product?->description ?? $this->descricao;
    }

    public function getDiasAVencerAttribute()
    {
        if (!$this->data_validade) {
            return null;
        }
        return now()->diffInDays($this->data_validade, false);
    }
}
