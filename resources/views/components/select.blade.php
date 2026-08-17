@props([
    'label' => null,
    'name' => null,
])

@php
    $id = $attributes->get('id') ?? $name;
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-label-bold font-bold text-primary">{{ $label }}</label>
    @endif

    <select
        id="{{ $id }}"
        @if ($name) name="{{ $name }}" @endif
        {{ $attributes->merge(['class' => 'h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 text-body-md outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20']) }}
    >
        {{ $slot }}
    </select>
</div>
