<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => array_merge(
                $this->passwordRules(),
                [
                    Rule::notIn([$user->password]), // ห้ามใช้รหัสผ่านเดิม (hash)
                    function ($attribute, $value, $fail) use ($user) {
                        // ตรวจสอบว่า password ใหม่ไม่เหมือนกับ password เดิม
                        if (Hash::check($value, $user->password)) {
                            $fail('รหัสผ่านใหม่ต้องไม่เหมือนกับรหัสผ่านเดิม');
                        }
                    }
                ]
            ),
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
