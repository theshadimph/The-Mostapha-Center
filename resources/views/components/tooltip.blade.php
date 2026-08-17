@props([
    'text',
])

<div x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false" class="relative inline-flex">
    {{ $slot }}

    <div
        x-show="show"
        x-cloak
        x-transition.opacity
        class="pointer-events-none absolute -top-9 left-1/2 z-50 -translate-x-1/2 whitespace-nowrap rounded-lg bg-inverse-surface px-2.5 py-1.5 text-caption text-inverse-on-surface shadow-lg"
    >
        {{ $text }}
    </div>
</div>
