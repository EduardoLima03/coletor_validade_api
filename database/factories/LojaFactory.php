<?php

namespace Database\Factories;

use App\Models\Loja;
use Illuminate\Database\Eloquent\Factories\Factory;

class LojaFactory extends Factory
{
    protected $model = Loja::class;

    public function definition()
    {
        return [
            'nome' => fake()->company(),
        ];
    }
}
