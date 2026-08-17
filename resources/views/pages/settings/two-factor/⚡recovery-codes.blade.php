<?php

use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public array $recoveryCodes = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(auth()->user());

        $this->loadRecoveryCodes();
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        $user = auth()->user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception) {
                $this->addError('recoveryCodes', 'Failed to load recovery codes');

                $this->recoveryCodes = [];
            }
        }
    }
}; ?>

<div class="space-y-6 rounded-xl border border-outline-variant py-6 shadow-sm" wire:cloak x-data="{ showRecoveryCodes: false }">
    <div class="space-y-2 px-6">
        <div class="flex items-center gap-2">
            <x-icon name="lock" class="text-lg text-on-surface-variant" />
            <x-heading size="md">{{ __('2FA recovery codes') }}</x-heading>
        </div>
        <p class="text-body-md text-on-surface-variant">
            {{ __('Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}
        </p>
    </div>

    <div class="px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <x-button x-show="!showRecoveryCodes" variant="primary" @click="showRecoveryCodes = true" aria-expanded="false" aria-controls="recovery-codes-section">
                <x-icon name="visibility" class="text-lg" />
                {{ __('View recovery codes') }}
            </x-button>

            <x-button x-show="showRecoveryCodes" x-cloak variant="primary" @click="showRecoveryCodes = false" aria-expanded="true" aria-controls="recovery-codes-section">
                <x-icon name="visibility_off" class="text-lg" />
                {{ __('Hide recovery codes') }}
            </x-button>

            @if (filled($recoveryCodes))
                <x-button x-show="showRecoveryCodes" x-cloak variant="outline" wire:click="regenerateRecoveryCodes">
                    <x-icon name="autorenew" class="text-lg" />
                    {{ __('Regenerate codes') }}
                </x-button>
            @endif
        </div>

        <div x-show="showRecoveryCodes" x-cloak x-transition id="recovery-codes-section" class="relative overflow-hidden" x-bind:aria-hidden="!showRecoveryCodes">
            <div class="mt-3 space-y-3">
                @error('recoveryCodes')
                    <div class="flex items-center gap-3 rounded-xl border border-error/30 bg-error-container px-4 py-3 text-on-error-container">
                        <x-icon name="error" class="shrink-0" />
                        <p class="text-body-md">{{ $message }}</p>
                    </div>
                @enderror

                @if (filled($recoveryCodes))
                    <div class="grid gap-1 rounded-lg bg-surface-container-low p-4 font-mono text-sm" role="list" aria-label="{{ __('Recovery codes') }}">
                        @foreach ($recoveryCodes as $code)
                            <div role="listitem" class="select-text" wire:loading.class="animate-pulse opacity-50">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-on-surface-variant">
                        {{ __('Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate codes above.') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
