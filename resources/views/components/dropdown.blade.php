@props([
    'align' => 'start',
    'width' => 'w-56',
])

<div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative inline-block">
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click="open = false"
        {{ $attributes->merge(['class' => 'absolute z-50 mt-2 '.$width.' rounded-xl border border-outline-variant bg-surface-container-lowest shadow-lg py-2 '.($align === 'end' ? 'left-0' : 'right-0')]) }}
    >
        {{ $menu }}
    </div>
</div>
