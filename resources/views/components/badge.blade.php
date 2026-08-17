@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'success' => 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
        'warning' => 'bg-secondary-fixed text-on-secondary-fixed-variant',
        'error' => 'bg-error-container text-on-error-container',
        'primary' => 'bg-primary-container text-on-primary-container',
        'neutral' => 'bg-surface-container-high text-on-surface',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-3 py-1 rounded-full text-caption font-bold '.($variants[$variant] ?? $variants['neutral'])]) }}>
    {{ $slot }}
</span>
