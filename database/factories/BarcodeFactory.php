<?php

namespace Database\Factories;

use App\Models\Barcode;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class BarcodeFactory extends Factory
{
    protected $model = Barcode::class;

    public function definition()
    {
        return [
            'ean' => fake()->unique()->numerify('##############'),
            'product_id' => Product::factory(),
        ];
    }
}
