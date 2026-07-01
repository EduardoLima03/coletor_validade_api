<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecolhimentoRegra extends Model
{
    use HasFactory;

    protected $table = 'recolhimento_regras';

    protected $fillable = [
        'dia_semana',
        'dias_antecedencia',
        'ativo',
    ];

    protected $casts = [
        'dia_semana' => 'integer',
        'dias_antecedencia' => 'integer',
        'ativo' => 'boolean',
    ];

    public static array $diasSemana = [
        0 => 'Domingo',
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
    ];

    public function getDiaSemanaNomeAttribute(): string
    {
        return self::$diasSemana[$this->dia_semana] ?? 'Desconhecido';
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorDia($query, int $diaSemana)
    {
        return $query->where('dia_semana', $diaSemana);
    }

    public static function diasAntecedenciaParaHoje(): ?int
    {
        $hoje = now()->dayOfWeek;
        $regra = self::ativos()->porDia($hoje)->first();
        return $regra?->dias_antecedencia;
    }
}
