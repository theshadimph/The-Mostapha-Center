@php
    $items = [
        ['route' => 'profile.edit', 'pattern' => 'profile.edit', 'label' => __('Profile')],
        ['route' => 'security.edit', 'pattern' => 'security.edit', 'label' => __('Security')],
        ['route' => 'appearance.edit', 'pattern' => 'appearance.edit', 'label' => __('Appearance')],
    ];
@endphp

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <nav aria-label="{{ __('Settings') }}" class="flex flex-col gap-1">
            @foreach ($items as $item)
                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    class="rounded-xl px-4 py-2.5 text-body-md transition-colors {{ request()->routeIs($item['pattern']) ? 'bg-primary-container font-bold text-on-primary-container' : 'text-on-surface-variant hover:bg-surface-container-high' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    <div class="my-4 h-px w-full bg-outline-variant md:hidden"></div>

    <div class="flex-1 self-stretch max-md:pt-6">
        <x-heading size="md">{{ $heading ?? '' }}</x-heading>
        <x-subheading>{{ $subheading ?? '' }}</x-subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
