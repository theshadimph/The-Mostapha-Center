@props([
    'name',
])

<span {{ $attributes->merge(['class' => 'contents']) }} x-on:click="$dispatch('open-modal', @js($name))">
    {{ $slot }}
</span>
