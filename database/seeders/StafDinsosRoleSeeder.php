<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StafDinsosRoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => UserRole::STAF_DINSOS->value]);

        foreach (UserRole::STAF_DINSOS->permissions() as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $role->syncPermissions(UserRole::STAF_DINSOS->permissions());
    }
}
