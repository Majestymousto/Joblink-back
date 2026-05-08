<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate([
            'name'      => 'Seydou Hamadou Moustapha',
            'email'     => 'seydmoustomhd82@gmail.com',
            'password'  => '12345678',
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // ajouter tes informations pour te connecter @Oumar
        User::firstOrCreate([
            'name'      => 'Admin Secondaire',
            'email'     => 'admin2@example.com',
            'password'  => 'Password1',
            'role'      => 'admin',
            'is_active' => true,
        ]);
    }
}