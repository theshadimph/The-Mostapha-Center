@props([
    'variant' => 'primary',
    'href' => null,
    'size' => 'md',
])

@php
    $variants = [
        'primary' => 'bg-primary text-on-primary hover:bg-primary-container',
        'secondary' => 'bg-secondary-container text-on-secondary-container hover:opacity-90',
        'outline' => 'border-2 border-primary text-primary hover:bg-primary/5',
        'ghost' => 'text-on-surface-variant hover:bg-surface-container-high',
        'danger' => 'bg-error text-on-error hover:opacity-90',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-label-sm',
        'md' => 'px-6 py-2.5 text-label-bold',
        'lg' => 'px-8 py-4 text-body-lg',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 rounded-xl font-bold transition-all active:scale-95 disabled:opacity-50 disabled:pointer-events-none cursor-pointer '
        .($variants[$variant] ?? $variants['primary']).' '
        .($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
