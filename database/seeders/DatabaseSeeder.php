<?php

namespace Database\Seeders;

use App\Models\Pen;
use App\Models\Role;
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
        // เรียก Seeder อื่น ๆ ที่สร้างไว้
        $this->call([
            //FarmSeeder::class,
            //BarnSeeder::class,
            //PenSeeder::class,
            StorehouseNewBatchSeeder::class,
            //StorehouseSeeder::class,
            //RoleSeeder::class,
            //PermissionSeeder::class,
            //RolePermissionSeeder::class,
            //UserSeeder::class,
            //RoleUserSeeder::class,
        ]);
    }
}
