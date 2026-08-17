<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col overflow-x-hidden bg-background text-on-background antialiased">
        <!-- TopAppBar -->
        <header class="sticky top-0 z-50 border-b border-outline-variant bg-surface-container-lowest">
            <div class="mx-auto flex w-full max-w-container-max items-center justify-between px-margin-mobile py-4 md:px-margin-desktop">
                <div class="flex items-center gap-8">
                    <x-app-logo href="{{ route('home') }}" wire:navigate />

                    <nav class="hidden gap-6 md:flex">
                        <a href="{{ route('home') }}" wire:navigate class="font-bold {{ request()->routeIs('home') ? 'border-b-2 border-primary pb-1 text-primary' : 'text-on-surface-variant transition-colors hover:text-primary' }}">{{ __('الرئيسية') }}</a>
                        <a href="{{ route('search') }}" wire:navigate class="font-bold {{ request()->routeIs('search') || request()->routeIs('books.*') ? 'border-b-2 border-primary pb-1 text-primary' : 'text-on-surface-variant transition-colors hover:text-primary' }}">{{ __('المكتبة') }}</a>
                    </nav>
                </div>

                <div class="flex items-center gap-2">
                    <x-theme-toggle />

                    @auth
                        <div class="mx-2 h-8 w-px bg-outline-variant"></div>
                        <x-desktop-user-menu :name="auth()->user()->name" />
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="mx-2 rounded-full bg-primary px-6 py-2 text-label-bold font-bold text-on-primary shadow-sm transition-all hover:opacity-90 active:scale-95">
                            {{ __('Log in') }}
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-container-max grow px-margin-mobile py-8 md:px-margin-desktop">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="mt-auto border-t border-outline-variant bg-surface-container-highest">
            <div class="mx-auto flex w-full max-w-container-max flex-col items-center justify-between gap-4 px-margin-mobile py-6 md:flex-row md:px-margin-desktop">
                <div class="flex flex-col items-center gap-1 text-center md:flex-row md:gap-4 md:text-right">
                    <span class="text-label-bold font-bold text-primary">{{ config('app.name', 'Laravel') }}</span>
                    <span class="text-label-sm text-on-surface-variant">© {{ now()->year }} {{ config('app.name', 'Laravel') }} — {{ __('جميع الحقوق محفوظة') }}</span>
                </div>

                <nav class="flex flex-wrap justify-center gap-6">
                    <a href="{{ route('about') }}" wire:navigate class="text-label-sm text-secondary transition-all hover:text-primary hover:underline">{{ __('عن المركز') }}</a>
                    <a href="{{ route('activities') }}" wire:navigate class="text-label-sm text-secondary transition-all hover:text-primary hover:underline">{{ __('الأنشطة') }}</a>
                    <a href="{{ route('donations') }}" wire:navigate class="text-label-sm text-secondary transition-all hover:text-primary hover:underline">{{ __('الوقف والتبرعات') }}</a>
                    <a href="{{ route('contact') }}" wire:navigate class="text-label-sm text-secondary transition-all hover:text-primary hover:underline">{{ __('تواصل معنا') }}</a>
                </nav>
            </div>
        </footer>

        <x-toast />

        @livewireScripts
    </body>
</html>
