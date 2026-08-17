<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Appearance settings')] class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-heading class="sr-only">{{ __('Appearance settings') }}</x-heading>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <div
            x-data="{
                options: [
                    { value: 'light', label: @js(__('Light')), icon: 'light_mode' },
                    { value: 'dark', label: @js(__('Dark')), icon: 'dark_mode' },
                ],
            }"
            class="inline-flex rounded-xl border border-outline-variant bg-surface-container-low p-1"
        >
            <template x-for="option in options" :key="option.value">
                <button
                    type="button"
                    @click="$store.appearance.set(option.value)"
                    :class="$store.appearance.value === option.value ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-surface-container-high'"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-label-bold font-bold transition-colors"
                >
                    <span class="material-symbols-outlined text-lg" x-text="option.icon"></span>
                    <span x-text="option.label"></span>
                </button>
            </template>
        </div>
    </x-pages::settings.layout>
</section>
