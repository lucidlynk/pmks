<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Kumpulkan semua permission unik dari semua role
        $allPermissions = collect(UserRole::cases())
            ->flatMap(fn(UserRole $role) => $role->permissions())
            ->unique();

        // Buat semua permission
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Buat role & assign permission
        foreach (UserRole::cases() as $roleEnum) {
            $role = Role::firstOrCreate(['name' => $roleEnum->value]);
            $role->syncPermissions($roleEnum->permissions());
        }
    }
}