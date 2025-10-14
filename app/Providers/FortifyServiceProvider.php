<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Custom login authentication
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                // ตรวจสอบสถานะการอนุมัติ
                if ($user->status === 'pending') {
                    throw ValidationException::withMessages([
                        'email' => ['บัญชีของคุณรอการอนุมัติจาก Admin กรุณารอสักครู่'],
                    ]);
                }

                if ($user->status === 'rejected') {
                    throw ValidationException::withMessages([
                        'email' => ['บัญชีของคุณถูกปฏิเสธ: ' . ($user->rejection_reason ?? 'ไม่ระบุเหตุผล')],
                    ]);
                }

                // ตรวจสอบว่ามี role หรือไม่
                if ($user->roles()->count() === 0) {
                    throw ValidationException::withMessages([
                        'email' => ['บัญชีของคุณยังไม่ได้รับมอบหมายบทบาท (Role) กรุณาติดต่อ Admin'],
                    ]);
                }

                return $user;
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
