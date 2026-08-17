@props([
    'name' => null,
    'initials' => null,
    'size' => 'md',
])

@php
    $initials ??= collect(explode(' ', trim($name ?? '')))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');

    $sizes = [
        'sm' => 'w-8 h-8 text-label-sm',
        'md' => 'w-10 h-10 text-label-md',
        'lg' => 'w-16 h-16 text-headline-md',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full bg-primary-container text-on-primary-container font-bold shrink-0 '.($sizes[$size] ?? $sizes['md'])]) }}>
    {{ $initials }}
</span>
