@props([
    'href' => null,
    'icon' => null,
    'type' => 'button',
])

@php
    $classes = 'flex w-full items-center gap-3 px-4 py-2.5 text-body-md text-on-surface hover:bg-surface-container-high transition-colors text-right cursor-pointer';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon :name="$icon" class="text-lg text-on-surface-variant" />
        @endif
        <span class="flex-1">{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon :name="$icon" class="text-lg text-on-surface-variant" />
        @endif
        <span class="flex-1">{{ $slot }}</span>
    </button>
@endif
