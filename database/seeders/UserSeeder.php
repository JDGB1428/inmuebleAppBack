<?php

namespace Database\Seeders;

use App\Models\User;
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

        $owner = User::create([
            'name' => 'PropetarioTest 1',
            'email' => 'owner@test.com',
            'password' => Hash::make('@password123'),
        ]);
        $owner->assignRole('owner');

        $owner2 = User::create([
            'name' => 'PropetarioTest 2',
            'email' => 'owner2@test2.com',
            'password' => Hash::make('@password123'),
        ]);
        $owner2->assignRole('owner');
    }
}
