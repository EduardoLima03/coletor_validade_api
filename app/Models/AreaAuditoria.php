<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaAuditoria extends Model
{
    protected $table = 'areas_auditoria';

    protected $fillable = [
        'loja_id',
        'nome',
        'descricao',
    ];

    public function loja()
    {
        return $this->belongsTo(Loja::class);
    }
}
