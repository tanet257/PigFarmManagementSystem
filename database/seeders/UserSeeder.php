<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        // ลบข้อมูลเก่า
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_user')->truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $staffRole = Role::where('name', 'staff')->first();

        $users = [
            [
                'name' => 'AdminUser',
                'email' => 'admin@example.com',
                'password' => Hash::make('12345678'),
                'status' => 'approved',
                'approved_by' => null,
                'approved_at' => now(),
                'phone' => '0123456789',
                'address' => 'Farm 1',
                'role' => $adminRole,
            ],
            [
                'name' => 'ManagerUser',
                'email' => 'manager@example.com',
                'password' => Hash::make('12345678'),
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'phone' => '0987654321',
                'address' => 'Farm 2',
                'role' => $managerRole,
            ],
            [
                'name' => 'StaffUser',
                'email' => 'staff@example.com',
                'password' => Hash::make('12345678'),
                'status' => 'approved',
                'approved_by' => 1, // admin user id
                'approved_at' => now(),
                'phone' => '0112233445',
                'address' => 'Farm 3',
                'role' => $staffRole,
            ],
        ];

        foreach ($users as $u) {
            $user = User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => $u['password'],
                'status' => $u['status'],
                'approved_by' => $u['approved_by'],
                'approved_at' => $u['approved_at'],
                'phone' => $u['phone'],
                'address' => $u['address'],
            ]);

            // Attach role
            if ($u['role']) {
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $u['role']->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
