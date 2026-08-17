@props([
    'name',
    'fill' => false,
])

<span
    {{ $attributes->merge(['class' => 'material-symbols-outlined']) }}
    @if ($fill) style="font-variation-settings: 'FILL' 1;" @endif
    aria-hidden="true"
>{{ $name }}</span>
