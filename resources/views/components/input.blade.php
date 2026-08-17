@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'icon' => null,
    'viewable' => false,
])

@php
    $id = $attributes->get('id') ?? $name;
    $hasError = $name && $errors->has($name);

    $inputClasses = 'w-full h-12 py-3 bg-surface-container-lowest border rounded-lg outline-none transition-all text-body-md '
        .($icon ? 'pr-11 ' : 'pr-4 ')
        .($viewable ? 'pl-11 ' : 'pl-4 ')
        .($hasError
            ? 'border-error focus:border-error focus:ring-2 focus:ring-error/20'
            : 'border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20');
@endphp

<div x-data="{ visible: false }" class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-label-bold font-bold text-primary">{{ $label }}</label>
    @endif

    <div class="relative">
        @if ($icon)
            <x-icon :name="$icon" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-xl" />
        @endif

        @if ($viewable)
            <input
                :type="visible ? 'text' : 'password'"
                id="{{ $id }}"
                @if ($name) name="{{ $name }}" @endif
                {{ $attributes->merge(['class' => $inputClasses]) }}
            />
            <button type="button" @click="visible = !visible" class="absolute left-3 top-1/2 -translate-y-1/2 text-outline hover:text-primary transition-colors" tabindex="-1">
                <x-icon name="visibility" x-show="!visible" class="text-xl" />
                <x-icon name="visibility_off" x-show="visible" x-cloak class="text-xl" />
            </button>
        @else
            <input
                type="{{ $type }}"
                id="{{ $id }}"
                @if ($name) name="{{ $name }}" @endif
                {{ $attributes->merge(['class' => $inputClasses]) }}
            />
        @endif
    </div>

    @if ($hasError)
        <p class="text-caption text-error">{{ $errors->first($name) }}</p>
    @endif
</div>
