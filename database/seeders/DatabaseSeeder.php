<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Validação',
                'position' => 'COLETOR',
                'email' => 'validacao@medeiros.api',
                'password' => Hash::make('360632'),
            ],
            [
                'name' => 'Carlos Lima',
                'position' => 'ADMIN',
                'email' => 'carlos@medeiros.api',
                'password' => Hash::make('3012api'),
            ],
            [
                'name' => 'Repositor',
                'position' => 'COLETOR',
                'email' => 'repositor@medeiros.api',
                'password' => Hash::make('360632'),
            ],
            [
                'name' => 'Promotor',
                'position' => 'COLETOR',
                'email' => 'promotor@medeiros.api',
                'password' => Hash::make('360632'),
            ],
            [
                'name' => 'Brigada de validade',
                'position' => 'COLETOR',
                'email' => 'brigada@medeiros.api',
                'password' => Hash::make('360632'),
            ],
            [
                'name' => 'Controladoria',
                'position' => 'GERENCIA',
                'email' => 'controladoria@medeiros.api',
                'password' => Hash::make('360632'),
            ]
        ]);
    }
}
