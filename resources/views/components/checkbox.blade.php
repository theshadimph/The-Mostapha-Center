@props([
    'label' => null,
    'name' => null,
    'checked' => false,
])

@php
    $id = $attributes->get('id') ?? $name;
@endphp

<label for="{{ $id }}" class="flex items-center gap-2 cursor-pointer group select-none">
    <span class="relative flex items-center">
        <input
            type="checkbox"
            id="{{ $id }}"
            @if ($name) name="{{ $name }}" @endif
            @checked($checked)
            {{ $attributes->merge(['class' => 'peer appearance-none w-5 h-5 border-2 border-outline-variant rounded-md checked:bg-primary checked:border-primary transition-all cursor-pointer']) }}
        />
        <x-icon name="check" fill class="absolute opacity-0 peer-checked:opacity-100 text-white text-[16px] pointer-events-none right-0.5" />
    </span>

    @if ($label)
        <span class="text-on-surface-variant group-hover:text-primary transition-colors">{{ $label }}</span>
    @endif
</label>
