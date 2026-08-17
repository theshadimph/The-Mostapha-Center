@props([
    'href' => '#',
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'text-primary font-bold hover:underline underline-offset-4 transition-colors']) }}>{{ $slot }}</a>
