<?php

use Livewire\Component;

new class extends Component {}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <x-heading size="md">{{ __('Delete account') }}</x-heading>
        <x-subheading>{{ __('Delete your account and all of its resources') }}</x-subheading>
    </div>

    <x-modal-trigger name="confirm-user-deletion">
        <x-button variant="danger" data-test="delete-user-button">
            {{ __('Delete account') }}
        </x-button>
    </x-modal-trigger>

    <livewire:pages::settings.delete-user-modal />
</section>
