<?php

namespace Database\Factories;

use App\Models\AreaAuditoria;
use Illuminate\Database\Eloquent\Factories\Factory;

class AreaAuditoriaFactory extends Factory
{
    protected $model = AreaAuditoria::class;

    public function definition()
    {
        return [
            'loja_id' => \App\Models\Loja::factory(),
            'nome' => fake()->unique()->words(2, true),
        ];
    }
}
