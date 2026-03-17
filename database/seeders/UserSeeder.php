<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Principal',
            'email' => 'admin@admin.com',
            'password' => Hash::make('@password123'), // Contraseña segura
        ]);

        // Asignar rol (si usas Spatie)
        $admin->assignRole('admin');

        $agent = User::create([
            'name' => 'Agente Inmobiliario',
            'email' => 'agente@test.com',
            'password' => Hash::make('@password123'),
        ]);
        $agent->assignRole('agent');
    }
}
