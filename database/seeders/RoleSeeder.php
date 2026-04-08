<?php

namespace Database\Seeders;

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
        $role_owner = Role::create(['name' => 'owner']);
        $role_client = Role::create(['name' => 'client']);

        // Inmueble
        Permission::create(['name' => 'ver inmuebles'])->assignRole($role_owner);
        Permission::create(['name' => 'crear inmuebles'])->syncRoles([$role_owner, $role_admin]);
        Permission::create(['name' => 'editar inmuebles'])->syncRoles([$role_owner, $role_admin]);
        Permission::create(['name' => 'eliminar inmuebles'])->syncRoles([$role_owner, $role_admin]);

        //Permisos especiales de admin
        Permission::create(['name' => 'ver papelera inmuebles'])->assignRole([$role_admin]);
        Permission::create(['name' => 'restaurar inmuebles'])->assignRole([$role_admin]);
        Permission::create(['name' => 'ver papelera perfiles'])->assignRole([$role_admin]);
        Permission::create(['name' => 'restaurar perfiles'])->assignRole([$role_admin]);



        //Perfiles
        Permission::create(['name' => 'ver todos los perfiles'])->assignRole($role_admin);
        Permission::create(['name' => 'editar perfil'])->syncRoles([$role_client, $role_owner]);
        Permission::create(['name' => 'crear perfil'])->syncRoles([$role_client, $role_owner]);
        Permission::create(['name' => 'ver perfil'])->syncRoles([$role_client, $role_owner]);
        Permission::create(['name' => 'banear perfil'])->assignRole($role_admin);


    }
}
