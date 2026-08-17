{{--
    A repeatable list of name inputs bound to a Livewire array property
    (e.g. public array $editors = ['']). Used for book fields that may have
    more than one person — editors, commentators, annotators — where each
    is just a name, not a full Author-style entity with its own page.
--}}
@props(['field', 'label', 'values'])

<div>
    <label class="mb-1.5 block text-label-bold font-bold text-primary">{{ $label }}</label>

    <div class="flex flex-col gap-2">
        @foreach ($values as $index => $value)
            <div class="flex gap-2">
                <input
                    type="text"
                    wire:model="{{ $field }}.{{ $index }}"
                    placeholder="{{ __('الاسم') }}"
                    class="h-11 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 text-body-md outline-none focus:border-primary"
                >
                <button
                    type="button"
                    wire:click="removeContributor('{{ $field }}', {{ $index }})"
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-error hover:bg-error/10"
                    aria-label="{{ __('حذف') }}"
                >
                    <x-icon name="close" class="text-lg" />
                </button>
            </div>
        @endforeach
    </div>

    <button type="button" wire:click="addContributor('{{ $field }}')" class="mt-2 text-caption font-bold text-primary hover:underline">
        {{ __('+ إضافة اسم') }}
    </button>
</div>
