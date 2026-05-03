<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            KecamatanSeeder::class,
            VillageSeeder::class,
            PmksCategorySeeder::class,
            PsksCategorySeeder::class,
        ]);
    }
}
