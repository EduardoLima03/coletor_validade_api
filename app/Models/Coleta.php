<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coleta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "loja_id", "area_auditoria_id", "user_id",
        "ean", "quantidade", "unidade", "data_validade", "datahora",
        "recolhido_em", "recolhido_quantidade", "recolhido_user_id",
    ];

    protected $casts = [
        "data_validade" => "date",
        "datahora" => "datetime",
        "deleted_at" => "datetime",
        "recolhido_em" => "datetime",
        "quantidade" => "decimal:2",
        "recolhido_quantidade" => "decimal:2",
    ];

    protected $appends = ["product_name", "valor_recolhido"];

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

    public function recolhidoUser()
    {
        return $this->belongsTo(User::class, 'recolhido_user_id');
    }

    public function barcode()
    {
        return $this->belongsTo(Barcode::class, "ean", "ean");
    }

    public function getProductNameAttribute(): ?string
    {
        return $this->barcode?->product?->description ?? 'Produto não encontrado';
    }

    public function getDiasAVencerAttribute()
    {
        if (!$this->data_validade) {
            return null;
        }
        return now()->diffInDays($this->data_validade, false);
    }

    public function getValorRecolhidoAttribute(): float
    {
        $custo = $this->barcode?->product?->custo ?? 0;
        $qtd = (float) ($this->recolhido_quantidade ?? 0);
        return round($custo * $qtd, 2);
    }

    public function scopeNaoRecolhidos($query)
    {
        return $query->whereNull('recolhido_em');
    }

    public function scopeRecolhidos($query)
    {
        return $query->whereNotNull('recolhido_em');
    }

    public function scopeAVencer($query, int $dias)
    {
        return $query->whereBetween('data_validade', [now()->startOfDay(), now()->startOfDay()->addDays($dias)]);
    }

    public function scopeDisponiveisParaRecolhimento($query, int $diasAntecedencia)
    {
        return $query->naoRecolhidos()->aVencer($diasAntecedencia);
    }

    public function getIsRecolhidoAttribute(): bool
    {
        return !is_null($this->recolhido_em);
    }
}
