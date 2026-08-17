<span {{ $attributes->merge(['class' => 'contents']) }} x-on:click="$dispatch('close-modal')">
    {{ $slot }}
</span>
