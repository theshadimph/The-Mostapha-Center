<x-layouts::auth :title="__('Log in')">
    <x-slot:sidePanel>
        <div class="relative flex h-full w-full flex-col items-center justify-end bg-gradient-to-br from-primary to-primary-container p-12 text-white">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 24px 24px;"></div>
            <x-icon name="lightbulb" fill class="!text-6xl mb-4" />
            <h2 class="text-headline-lg font-bold mb-2">{{ __('المعرفة قوة') }}</h2>
            <p class="text-body-lg text-center text-primary-fixed">{{ __('استكشف آلاف الكتب والمصادر الرقمية في بيئة ذكية متكاملة مصممة للباحثين والقراء المعاصرين.') }}</p>
        </div>
    </x-slot:sidePanel>

    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        {{-- @chisel-passkeys --}}
        <x-passkey-verify />
        {{-- @end-chisel-passkeys --}}

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <x-input
                name="email"
                icon="mail"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <x-input
                    name="password"
                    icon="lock"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <x-link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </x-link>
                @endif
            </div>

            <!-- Remember Me -->
            <x-checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <x-button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>

        {{-- @chisel-registration --}}
        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-on-surface-variant">
            <span>{{ __('Don\'t have an account?') }}</span>
            <x-link
                :href="route('register')"
                data-test="register-link"
                wire:navigate
            >
                {{ __('Sign up') }}
            </x-link>
        </div>
        {{-- @end-chisel-registration --}}
    </div>
</x-layouts::auth>
