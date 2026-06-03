<?php

namespace Database\Seeders;

use App\Models\Loja;
use Illuminate\Database\Seeder;

class LojaSeeder extends Seeder
{
    public function run(): void
    {
        $lojas = ['CD', 'LOJA 01', 'LOJA 02', 'LOJA 03', 'LOJA 04', 'LOJA 05'];

        foreach ($lojas as $loja) {
            Loja::updateOrCreate(
                ['nome' => $loja],
                ['nome' => $loja]
            );
        }
    }
}
