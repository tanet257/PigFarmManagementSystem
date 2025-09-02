<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('permissions')->insert([
            ['name' => 'view_pig', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'create_pig', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit_pig', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete_pig', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manage_feed', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manage_medicine', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'view_reports', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manage_users', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'assign_roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manage_notifications', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'access_settings', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
