<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('ลืมรหัสผ่าน? ไม่เป็นไร แค่บอกเราเรื่องที่อยู่อีเมลของคุณ และเราจะส่งลิงก์รีเซตรหัสผ่านให้คุณ ซึ่งจะช่วยให้คุณตั้งรหัสผ่านใหม่ได้') }}
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}" autocomplete="off">
            @csrf

            <div class="block">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                    required autofocus autocomplete="off" placeholder="กรอกอีเมลของคุณ" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-900">
                    ← กลับไปที่หน้า Login
                </a>
                <x-button>
                    {{ __('ส่งลิงก์รีเซตรหัสผ่าน') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
