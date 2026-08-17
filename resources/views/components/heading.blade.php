@props([
    'size' => 'md',
])

@php
    $sizes = [
        'xl' => 'text-headline-xl',
        'lg' => 'text-headline-lg',
        'md' => 'text-headline-md',
    ];
@endphp

<h2 {{ $attributes->merge(['class' => ($sizes[$size] ?? $sizes['md']).' font-bold text-primary']) }}>{{ $slot }}</h2>
