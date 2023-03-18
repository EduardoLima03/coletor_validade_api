<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        DB::table('users')->insert([
            [
                'name' => 'Validação',
                'function' => 'Validação',
                'email' => 'validacao@medeiros.api',
                'password' => Hash::make('360632'),
            ],
            [
                'name' => 'Carlos Lima',
                'function' => 'Adim',
                'email' => 'carlos@medeiros.api',
                'password' => Hash::make('3012api'),
            ],
            [
                'name' => 'Repositor',
                'function' => 'Repositor',
                'email' => 'repositor@medeiros.api',
                'password' => Hash::make('360632'),
            ],
            [
                'name' => 'Promotor',
                'function' => 'promotor',
                'email' => 'promotor@medeiros.api',
                'password' => Hash::make('360632'),
            ],
            [
                'name' => 'Brigada de validade',
                'function' => 'brigada',
                'email' => 'brigada@medeiros.api',
                'password' => Hash::make('360632'),
            ],
            [
                'name' => 'Controladoria',
                'function' => 'controladoria',
                'email' => 'controladoria@medeiros.api',
                'password' => Hash::make('360632'),
            ]
        ]);
    }
}
