<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'code' => fake()->unique()->numerify('#############'),
            'description' => fake()->words(3, true),
            'custo' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
