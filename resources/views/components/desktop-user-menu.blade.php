@props(['showRole' => true])

<x-dropdown align="start">
    <x-slot:trigger>
        <button type="button" class="group flex items-center gap-2 rounded-lg p-1 hover:bg-surface-container-high" data-test="sidebar-menu-button">
            <x-avatar :initials="auth()->user()->initials()" size="sm" />
            <div class="hidden text-right leading-tight lg:grid">
                <span class="truncate text-label-sm font-medium text-on-surface">{{ auth()->user()->name }}</span>
                @if ($showRole && auth()->user()->role)
                    <span class="truncate text-caption text-on-surface-variant">{{ auth()->user()->role->label() }}</span>
                @endif
            </div>
            <x-icon name="unfold_more" class="hidden text-lg text-on-surface-variant lg:block" />
        </button>
    </x-slot:trigger>

    <x-slot:menu>
        <div class="flex items-center gap-2 px-4 py-2.5">
            <x-avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
            <div class="grid flex-1 text-right leading-tight">
                <span class="truncate text-label-md font-bold text-on-surface">{{ auth()->user()->name }}</span>
                <span class="truncate text-caption text-on-surface-variant">{{ auth()->user()->email }}</span>
            </div>
        </div>

        <div class="my-1 border-t border-outline-variant"></div>

        <x-menu-item :href="route('profile.edit')" icon="settings" wire:navigate>
            {{ __('Settings') }}
        </x-menu-item>

        <div class="my-1 border-t border-outline-variant"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-menu-item type="submit" icon="logout" class="w-full" data-test="logout-button">
                {{ __('Log out') }}
            </x-menu-item>
        </form>
    </x-slot:menu>
</x-dropdown>
