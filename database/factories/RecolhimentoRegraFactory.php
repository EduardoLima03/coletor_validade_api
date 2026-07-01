<?php

namespace Database\Factories;

use App\Models\RecolhimentoRegra;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecolhimentoRegraFactory extends Factory
{
    protected $model = RecolhimentoRegra::class;

    public function definition()
    {
        return [
            'dia_semana' => fake()->numberBetween(0, 6),
            'dias_antecedencia' => fake()->numberBetween(1, 10),
            'ativo' => true,
        ];
    }
}
