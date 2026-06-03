<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loja extends Model
{
    protected $fillable = ["nome"];

    public function coletas()
    {
        return $this->hasMany(Coleta::class);
    }
}
