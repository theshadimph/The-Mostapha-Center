@props([
    'sidebar' => false,
])

<a {{ $attributes->merge(['class' => 'flex items-center gap-2 font-bold text-primary']) }}>
    <span class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-lg bg-primary text-white">
        <x-app-logo-icon class="size-5 fill-current" />
    </span>
    <span class="truncate text-label-bold">{{ config('app.name', 'Laravel') }}</span>
</a>
