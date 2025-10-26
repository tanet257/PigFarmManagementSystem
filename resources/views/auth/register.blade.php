<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}" autocomplete="off">
            @csrf

            <!-- Name -->
            <div style="margin-bottom: 1rem;">
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" type="text" name="name" :value="old('name')" required autofocus
                    autocomplete="off"
                    style="width:100%; padding:0.5rem; border:2px solid #FF5B22; border-radius:6px; transition:border-color 0.3s;"
                    onfocus="this.style.borderColor='#e65a00';" onblur="this.style.borderColor='#FF5B22';" />
            </div>

            <!-- Email -->
            <div style="margin-bottom: 1rem;">
                <x-label for="email" value="{{ __('Email (Gmail)') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autocomplete="off"
                    placeholder="example@gmail.com"
                    style="width:100%; padding:0.5rem; border:2px solid #FF5B22; border-radius:6px; transition:border-color 0.3s;"
                    onfocus="this.style.borderColor='#e65a00';" onblur="this.style.borderColor='#FF5B22';" />
                <small style="color: #666; display: block; margin-top: 0.25rem;">
                    ต้องใช้ Gmail (@gmail.com) เท่านั้น
                </small>
            </div>

            <!-- Phone -->
            <div style="margin-bottom: 1rem;">
                <x-label for="phone" value="{{ __('Phone') }}" />
                <x-input id="phone" type="text" name="phone" :value="old('phone')" required autocomplete="off"
                    style="width:100%; padding:0.5rem; border:2px solid #FF5B22; border-radius:6px; transition:border-color 0.3s;"
                    onfocus="this.style.borderColor='#e65a00';" onblur="this.style.borderColor='#FF5B22';" />
            </div>

            <!-- Address -->
            <div style="margin-bottom: 1rem;">
                <x-label for="address" value="{{ __('Address') }}" />
                <x-input id="address" type="text" name="address" :value="old('address')" required autocomplete="off"
                    style="width:100%; padding:0.5rem; border:2px solid #FF5B22; border-radius:6px; transition:border-color 0.3s;"
                    onfocus="this.style.borderColor='#e65a00';" onblur="this.style.borderColor='#FF5B22';" />
            </div>

            <!-- Password -->
            <div style="margin-bottom: 1rem;">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" type="password" name="password" required autocomplete="off"
                    style="width:100%; padding:0.5rem; border:2px solid #FF5B22; border-radius:6px; transition:border-color 0.3s;"
                    onfocus="this.style.borderColor='#e65a00';" onblur="this.style.borderColor='#FF5B22';" />
            </div>

            <!-- Confirm Password -->
            <div style="margin-bottom: 1rem;">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" type="password" name="password_confirmation" required
                    autocomplete="off"
                    style="width:100%; padding:0.5rem; border:2px solid #FF5B22; border-radius:6px; transition:border-color 0.3s;"
                    onfocus="this.style.borderColor='#e65a00';" onblur="this.style.borderColor='#FF5B22';" />
            </div>

            <!-- Terms -->
            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div style="margin-bottom: 1rem;">
                    <x-label for="terms">
                        <div style="display:flex; align-items:center; font-size:0.875rem;">
                            <x-checkbox name="terms" id="terms" required />
                            <div style="margin-left:0.5rem;">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                    'terms_of_service' =>
                                        '<a target="_blank" href="' .
                                        route('terms.show') .
                                        '" style="text-decoration:underline; color:#FF5B22;">' .
                                        __('Terms of Service') .
                                        '</a>',
                                    'privacy_policy' =>
                                        '<a target="_blank" href="' .
                                        route('policy.show') .
                                        '" style="text-decoration:underline; color:#FF5B22;">' .
                                        __('Privacy Policy') .
                                        '</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <!-- Buttons -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem;">
                <a href="{{ route('login') }}" style="font-size:0.875rem; color:#FF5B22; text-decoration:underline;">
                    Already registered?
                </a>

                <x-button type="submit"
                    style="background-color:#FF5B22; color:white; border:none; padding:0.5rem 1rem; border-radius:6px; cursor:pointer; font-weight:bold;"
                    onmouseover="this.style.backgroundColor='#e65a00';"
                    onmouseout="this.style.backgroundColor='#FF5B22';">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
