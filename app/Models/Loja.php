<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loja extends Model
{
    use HasFactory;

    protected $fillable = ["nome"];

    public function coletas()
    {
        return $this->hasMany(Coleta::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'loja_user');
    }

    public function areasAuditoria()
    {
        return $this->belongsToMany(AreaAuditoria::class, 'area_auditoria_loja');
    }
}
