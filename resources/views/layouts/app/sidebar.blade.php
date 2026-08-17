<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        @include('partials.head')
    </head>
    <body x-data="{ sidebarOpen: false }" class="bg-background text-on-background">
        <!-- TopAppBar -->
        <header class="fixed inset-x-0 top-0 z-50 h-16 border-b border-outline-variant bg-surface-container-lowest">
            <div class="flex h-full items-center justify-between px-margin-mobile md:px-margin-desktop">
                <div class="flex items-center gap-4">
                    <button class="rounded-full p-2 hover:bg-surface-container-high lg:hidden" @click="sidebarOpen = true">
                        <x-icon name="menu" />
                    </button>
                    <x-app-logo href="{{ route('home') }}" wire:navigate />
                </div>

                <div class="flex items-center gap-2">
                    <x-theme-toggle />
                    <div class="mx-2 hidden h-8 w-px bg-outline-variant lg:block"></div>
                    <x-desktop-user-menu :name="auth()->user()->name" />
                </div>
            </div>
        </header>

        <!-- Mobile drawer overlay -->
        <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-on-background/40 lg:hidden"></div>

        <!-- SideNavBar -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
            class="fixed right-0 top-16 z-40 flex h-[calc(100vh-4rem)] w-64 flex-col gap-1 overflow-y-auto border-l border-outline-variant bg-surface-container-low px-4 pb-6 pt-6 transition-transform duration-200"
        >
            <nav class="flex flex-1 flex-col gap-1">
                <p class="px-4 pb-1 text-caption font-bold uppercase tracking-wider text-on-surface-variant">{{ __('لوحة التحكم') }}</p>

                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    class="flex items-center gap-3 rounded-xl px-4 py-3 font-bold transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-container text-on-primary-container' : 'text-on-surface-variant hover:bg-surface-container-high' }}"
                >
                    <x-icon name="home" />
                    <span class="text-label-md">{{ __('الرئيسية') }}</span>
                </a>

                @can('manage-catalog')
                    <p class="px-4 pb-1 pt-4 text-caption font-bold uppercase tracking-wider text-on-surface-variant">{{ __('المكتبة') }}</p>

                    <a href="{{ route('admin.books.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl px-4 py-3 font-bold transition-colors {{ request()->routeIs('admin.books.*') ? 'bg-primary-container text-on-primary-container' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                        <x-icon name="auto_stories" />
                        <span class="text-label-md">{{ __('الكتب') }}</span>
                    </a>
                    <a href="{{ route('admin.authors.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl px-4 py-3 font-bold transition-colors {{ request()->routeIs('admin.authors.*') ? 'bg-primary-container text-on-primary-container' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                        <x-icon name="person" />
                        <span class="text-label-md">{{ __('المؤلفون') }}</span>
                    </a>
                    <a href="{{ route('admin.categories.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl px-4 py-3 font-bold transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-primary-container text-on-primary-container' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                        <x-icon name="category" />
                        <span class="text-label-md">{{ __('الأقسام العلمية') }}</span>
                    </a>
                    <a href="{{ route('admin.sections.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl px-4 py-3 font-bold transition-colors {{ request()->routeIs('admin.sections.*') ? 'bg-primary-container text-on-primary-container' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                        <x-icon name="shelves" />
                        <span class="text-label-md">{{ __('القاعات والأرفف') }}</span>
                    </a>
                    <a href="{{ route('admin.import.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl px-4 py-3 font-bold transition-colors {{ request()->routeIs('admin.import.*') ? 'bg-primary-container text-on-primary-container' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                        <x-icon name="upload_file" />
                        <span class="text-label-md">{{ __('استيراد من إكسل') }}</span>
                    </a>
                @endcan

                @can('manage-users')
                    <p class="px-4 pb-1 pt-4 text-caption font-bold uppercase tracking-wider text-on-surface-variant">{{ __('الإدارة') }}</p>

                    <a href="{{ route('admin.users.index') }}" wire:navigate class="flex items-center gap-3 rounded-xl px-4 py-3 font-bold transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-primary-container text-on-primary-container' : 'text-on-surface-variant hover:bg-surface-container-high' }}">
                        <x-icon name="admin_panel_settings" />
                        <span class="text-label-md">{{ __('المستخدمون') }}</span>
                    </a>
                @endcan
            </nav>

            <div class="mt-auto flex flex-col gap-1 border-t border-outline-variant pt-4">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3 rounded-xl px-4 py-3 text-on-surface-variant transition-colors hover:bg-surface-container-high">
                    <x-icon name="public" />
                    <span class="text-label-md">{{ __('عرض الموقع العام') }}</span>
                </a>
            </div>
        </aside>

        {{ $slot }}

        <x-toast />

        @livewireScripts
    </body>
</html>
