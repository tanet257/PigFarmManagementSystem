<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/@gmail\.com$/'  // ต้องเป็น Gmail เท่านั้น
            ],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ], [
            'email.regex' => 'กรุณาใช้ Gmail (@gmail.com) สำหรับการลงทะเบียน',
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'] ?? null,
            'address' => $input['address'] ?? null,
            'password' => Hash::make($input['password']),
            'status' => 'pending', // รอการอนุมัติจาก Admin
        ]);

        // ส่ง notification ไปยัง admin
        $admins = User::where('usertype', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'user_registration',
                'user_id' => $admin->id,
                'related_user_id' => $user->id,
                'title' => 'ผู้ใช้ใหม่ลงทะเบียน',
                'message' => "{$user->name} ({$user->email}) ลงทะเบียนใหม่และรอการอนุมัติ",
                'url' => route('user_management.index'),
                'is_read' => false,
            ]);
        }

        return $user;
    }
}
