<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'position',
        'email',
        'password',
        'coleta_edit',
        'coleta_delete',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'coleta_edit' => 'boolean',
        'coleta_delete' => 'boolean',
    ];

    public function lojas()
    {
        return $this->belongsToMany(Loja::class, 'loja_user');
    }

    public function lojasAcesso()
    {
        if ($this->position === 'ADMIN') {
            return Loja::orderBy('nome')->get();
        }

        $lojasAssign = $this->lojas()->orderBy('nome')->get();

        if ($lojasAssign->isNotEmpty()) {
            return $lojasAssign;
        }

        return Loja::orderBy('nome')->get();
    }

    public function lojasAcessoIds(): array
    {
        $lojas = $this->lojasAcesso();
        if ($lojas instanceof \Illuminate\Support\Collection) {
            return $lojas->pluck('id')->toArray();
        }
        return $lojas->pluck('id')->toArray();
    }

    public function podeEditarColeta(): bool
    {
        return $this->position === 'ADMIN' || $this->coleta_edit;
    }

    public function podeExcluirColeta(): bool
    {
        return $this->position === 'ADMIN' || $this->coleta_delete;
    }
}
