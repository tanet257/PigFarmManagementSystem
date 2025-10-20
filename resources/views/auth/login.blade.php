<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" autocomplete="off">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                    autofocus autocomplete="off"
                    style="
            border: 2px solid #FF5B22;      /* สีขอบตามธีมส้ม */
            border-radius: 6px;              /* มุมโค้ง */
            padding: 0.5rem;                 /* ระยะขอบภายใน */
            transition: border-color 0.3s;   /* ให้ขอบเปลี่ยนสีเนียน */
        "
                    onfocus="this.style.borderColor='#e65a00';" onblur="this.style.borderColor='#FF5B22';" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="off"
                    style="
            border: 2px solid #FF5B22;
            border-radius: 6px;
            padding: 0.5rem;
            transition: border-color 0.3s;
        "
                    onfocus="this.style.borderColor='#e65a00';" onblur="this.style.borderColor='#FF5B22';" />
            </div>


            <div class="mt-4 text-right">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-indigo-600 hover:text-indigo-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            {{-- ปุ่มอยู่ใต้ forgot password และกว้างเท่าช่องกรอก --}}
            <div class="mt-4">
                <x-button class="w-full justify-center" style="background-color: #FF5B22; color: white; border: none;">
                    {{ __('Log in') }}
                </x-button>
            </div>


            <div class="mt-3 text-center">
                <span class="text-sm text-gray-600">ยังไม่มีบัญชี?</span>
                <a href="{{ route('register') }}"
                    class="underline text-sm text-indigo-600 hover:text-indigo-800 font-medium ml-1">
                    สมัครสมาชิก
                </a>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
