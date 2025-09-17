<?php

namespace Database\Seeders;

use App\Models\Pen;
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
            StorehouseSeeder::class
            //PenSeeder::class,
            //RoleSeeder::class,
            //PermissionSeeder::class,
            //RolePermissionSeeder::class,
        ]);
    }
}
