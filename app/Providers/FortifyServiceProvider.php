<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\Fortify\LoginResponse;
use App\Models\User;
use App\Mail\ResetPasswordMail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override default LoginResponse
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        // Override default RegisterResponse
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                return redirect()->route('registration.pending');
            }
        });

        // Override default LogoutResponse - ให้ไปที่ login แทน home
        // และป้องกันการ back button
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                return redirect()->route('login')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            }
        });
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

        // Override password reset notification
        ResetPasswordNotification::toMailUsing(function ($notifiable, $token) {
            $resetUrl = url('password/reset/' . $token . '?email=' . urlencode($notifiable->getEmailForPasswordReset()));

            return (new MailMessage)
                ->markdown('emails.reset_password', [
                    'actionUrl' => $resetUrl,
                    'userName' => $notifiable->name ?? $notifiable->email,
                    'expiresAt' => now()->addMinutes(60),
                ])
                ->subject('รีเซตรหัสผ่าน - Pig Farm Management System');
        });

        // Redirect after registration
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // Fortify password reset views
        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot_password');
        });

        Fortify::resetPasswordView(function ($request) {
            return view('auth.reset_password', ['request' => $request]);
        });

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
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
