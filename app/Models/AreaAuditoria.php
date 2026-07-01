<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaAuditoria extends Model
{
    protected $table = 'areas_auditoria';

    protected $fillable = [
        'nome',
        'descricao',
    ];

    public function lojas()
    {
        return $this->belongsToMany(Loja::class, 'area_auditoria_loja');
    }
}
