<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            LojaSeeder::class,
        ]);

        $users = [
            ['name' => 'Validação',       'position' => 'COLETOR',  'email' => 'validacao@medeiros.api',       'password' => '360632'],
            ['name' => 'Carlos Lima',      'position' => 'ADMIN',    'email' => 'carlos@medeiros.api',         'password' => '3012api'],
            ['name' => 'Repositor',        'position' => 'COLETOR',  'email' => 'repositor@medeiros.api',      'password' => '360632'],
            ['name' => 'Promotor',         'position' => 'COLETOR',  'email' => 'promotor@medeiros.api',       'password' => '360632'],
            ['name' => 'Brigada de validade', 'position' => 'COLETOR', 'email' => 'brigada@medeiros.api',     'password' => '360632'],
            ['name' => 'Controladoria',    'position' => 'GERENCIA', 'email' => 'controladoria@medeiros.api',  'password' => '360632'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'position' => $user['position'],
                    'password' => bcrypt($user['password']),
                ]
            );
        }
    }
}
