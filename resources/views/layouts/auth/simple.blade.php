<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        @include('partials.head')
    </head>
    <body class="relative flex min-h-screen flex-col items-center justify-center overflow-x-hidden bg-background text-on-background antialiased">
        <div class="fixed right-4 top-4 z-20">
            <x-theme-toggle />
        </div>

        <!-- Subtle background elements -->
        <div
            class="pointer-events-none fixed inset-0 opacity-5"
            style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M54.826 22.528h-4.826v18.444h4.826c1.477 0 2.674-1.197 2.674-2.674v-13.096c0-1.477-1.197-2.674-2.674-2.674zM4.174 22.528h4.826v18.444H4.174C2.697 40.972 1.5 39.775 1.5 38.298v-13.096c0-1.477 1.197-2.674 2.674-2.674zM24.5 15h11v30h-11V15zm-14 5h8v20h-8V20zm28 0h8v20h-8V20z' fill='%230f5238' fill-opacity='1' fill-rule='evenodd'/%3E%3C/svg%3E&quot;)"
        ></div>
        <div class="pointer-events-none fixed -top-24 -left-24 h-96 w-96 rounded-full bg-primary-fixed-dim/20 blur-[120px]"></div>
        <div class="pointer-events-none fixed -bottom-24 -right-24 h-96 w-96 rounded-full bg-secondary-container/10 blur-[120px]"></div>

        {{-- Side illustration panel, opted into by the login page only (matches the Stitch login mockup) --}}
        @isset($sidePanel)
            <div class="fixed left-0 top-0 hidden h-full w-[35%] overflow-hidden bg-surface-container-low lg:block">
                {{ $sidePanel }}
            </div>
        @endisset

        <main class="relative z-10 w-full max-w-md px-margin-mobile md:px-0">
            <div class="mb-10 text-center">
                <a href="{{ route('home') }}" class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-primary to-primary-container text-white shadow-lg shadow-primary/20" wire:navigate>
                    <x-app-logo-icon class="h-10 w-10 fill-current" />
                </a>
                <h1 class="mt-6 text-headline-lg font-bold text-primary">{{ config('app.name', 'Laravel') }}</h1>
            </div>

            <div class="rounded-xl border border-primary/10 bg-surface-container-lowest/90 p-8 shadow-xl shadow-primary/5 backdrop-blur-md">
                {{ $slot }}
            </div>

            <footer class="mt-12 text-center text-caption text-on-surface-variant opacity-60">
                <p>© {{ now()->year }} {{ config('app.name', 'Laravel') }} — {{ __('جميع الحقوق محفوظة') }}</p>
            </footer>
        </main>

        <x-toast />

        @livewireScripts
    </body>
</html>
