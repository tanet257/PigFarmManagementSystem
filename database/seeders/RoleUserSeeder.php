<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ensure roles exist
        $adminRole = Role::where('name', 'admin')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $managerRole = Role::where('name', 'manager')->first();

        // Attach admin role to first user
        $firstUser = User::first();
        if ($firstUser && $adminRole) {
            DB::table('role_user')->updateOrInsert([
                'user_id' => $firstUser->id,
                'role_id' => $adminRole->id,
            ], ['created_at' => now(), 'updated_at' => now()]);
        }

        // Attach manager role to second user
        $secondUser = User::skip(1)->first();
        if ($secondUser && $managerRole) {
            DB::table('role_user')->updateOrInsert([
                'user_id' => $secondUser->id,
                'role_id' => $managerRole->id,
            ], ['created_at' => now(), 'updated_at' => now()]);
        }

        // Attach staff role to other users
        if ($staffRole) {
            $otherUsers = User::whereNotIn('id', [$firstUser->id ?? 0, $secondUser->id ?? 0])->get();
            foreach ($otherUsers as $u) {
                DB::table('role_user')->updateOrInsert([
                    'user_id' => $u->id,
                    'role_id' => $staffRole->id,
                ], ['created_at' => now(), 'updated_at' => now()]);
            }
        }
    }
}
