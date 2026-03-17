<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role_admin = Role::create(['name' => 'admin']);
        $role_client = Role::create(['name' => 'client']);
        $role_agent = Role::create(['name' => 'agent']);

        // Inmueble
        Permission::create(['name' => 'ver inmuebles'])->assignRole($role_client);
        Permission::create(['name' => 'crear inmuebles'])->syncRoles([$role_agent, $role_admin]);
        Permission::create(['name' => 'editar inmuebles'])->syncRoles([$role_agent, $role_admin]);
        Permission::create(['name' => 'eliminar inmuebles'])->syncRoles([$role_agent, $role_admin]);

        //Permisos especiales de admin
        Permission::create(['name' => 'ver papelera inmuebles'])->assignRole([$role_admin]);
        Permission::create(['name' => 'restaurar inmuebles'])->assignRole([$role_admin]);

        // Usuarios
        Permission::create(['name' => 'ver usuarios'])->assignRole($role_admin);
        Permission::create(['name' => 'banear usuarios'])->assignRole($role_admin);
        Permission::create(['name' => 'crear usuarios'])->assignRole($role_admin);
        Permission::create(['name' => 'editar usuarios'])->assignRole($role_admin);

        //Perfiles
        Permission::create(['name' => 'ver todos los perfiles'])->assignRole($role_admin);
        Permission::create(['name' => 'editar perfil'])->syncRoles([$role_agent, $role_client]);
        Permission::create(['name' => 'crear perfil'])->syncRoles([$role_agent, $role_client]);
        Permission::create(['name' => 'banear perfil'])->assignRole($role_admin);


    }
}
