@props([
    'name',
    'show' => false,
    'wireModel' => null,
])

{{--
    Replaces flux:modal. By default listens for window "open-modal" / "close-modal"
    events carrying the modal's name as either a plain string detail (Alpine
    `$dispatch('open-modal', 'name')`, used by <x-modal-trigger>) or an object
    `{ name: '...' }` (Livewire `$this->dispatch('close-modal', name: '...')`),
    so most Livewire component call sites need no changes.

    Pass `wire-model="propertyName"` instead when a component toggles the modal
    directly through a public boolean property (entangled two-way) rather than
    through the open/close event bus.
--}}
<div
    x-data="{ open: @if ($wireModel) @entangle($wireModel) @else @js((bool) $show) @endif }"
    x-show="open"
    x-cloak
    style="display: none"
    @if (! $wireModel)
        @open-modal.window="if ((typeof $event.detail === 'string' ? $event.detail : $event.detail?.name) === @js($name)) open = true"
        @close-modal.window="if (!$event.detail || (typeof $event.detail === 'string' ? $event.detail : $event.detail?.name) === @js($name)) open = false"
    @endif
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
>
    <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-on-background/40 backdrop-blur-sm"></div>

    <div
        x-show="open"
        x-transition
        @click.outside="open = false"
        x-effect="if (open) $nextTick(() => $el.querySelector('input, textarea, select, button')?.focus())"
        {{ $attributes->merge(['class' => 'relative w-full max-w-lg rounded-2xl bg-surface-container-lowest shadow-2xl p-8 max-h-[90vh] overflow-y-auto']) }}
    >
        {{ $slot }}
    </div>
</div>
