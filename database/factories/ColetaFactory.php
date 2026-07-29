<?php

namespace Database\Factories;

use App\Models\Coleta;
use App\Models\Loja;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ColetaFactory extends Factory
{
    protected $model = Coleta::class;

    public function definition()
    {
        return [
            'loja_id' => Loja::factory(),
            'user_id' => User::factory(),
            'area_auditoria_id' => null,
            'descricao' => fake()->words(3, true),
            'ean' => fake()->unique()->numerify('##############'),
            'quantidade' => (string) fake()->numberBetween(1, 100),
            'unidade' => 'un',
            'data_validade' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'datahora' => now(),
        ];
    }
}
