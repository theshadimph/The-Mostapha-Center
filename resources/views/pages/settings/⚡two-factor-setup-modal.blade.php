<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showVerificationStep = false;

    public bool $setupComplete = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    /**
     * Mount the component.
     */
    public function mount(bool $requiresConfirmation): void
    {
        $this->requiresConfirmation = $requiresConfirmation;
    }

    #[On('start-two-factor-setup')]
    public function startTwoFactorSetup(): void
    {
        $enableTwoFactorAuthentication = app(EnableTwoFactorAuthentication::class);
        $enableTwoFactorAuthentication(auth()->user());

        $this->loadSetupData();
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = auth()->user()?->fresh();

        try {
            if (! $user || ! $user->two_factor_secret) {
                throw new Exception('Two-factor setup secret is not available.');
            }

            $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
        $this->dispatch('two-factor-enabled');
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->setupComplete = true;

        $this->closeModal();

        $this->dispatch('two-factor-enabled');
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showVerificationStep',
            'setupComplete',
        );

        $this->resetErrorBag();
    }

    /**
     * Get the current modal configuration state.
     */
    #[Computed]
    public function modalConfig(): array
    {
        if ($this->setupComplete) {
            return [
                'title' => __('Two-factor authentication enabled'),
                'description' => __('Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'),
                'buttonText' => __('Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify authentication code'),
                'description' => __('Enter the 6-digit code from your authenticator app.'),
                'buttonText' => __('Continue'),
            ];
        }

        return [
            'title' => __('Enable two-factor authentication'),
            'description' => __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'),
            'buttonText' => __('Continue'),
        ];
    }
}; ?>

<x-modal name="two-factor-setup-modal">
    <div class="space-y-6">
        <div class="flex flex-col items-center space-y-4">
            <div class="flex size-16 items-center justify-center rounded-full bg-primary-fixed">
                <x-icon name="qr_code_2" class="text-3xl text-on-primary-fixed-variant" />
            </div>

            <div class="space-y-2 text-center">
                <x-heading size="md">{{ $this->modalConfig['title'] }}</x-heading>
                <p class="text-body-md text-on-surface-variant">{{ $this->modalConfig['description'] }}</p>
            </div>
        </div>

        @if ($showVerificationStep)
            <div class="space-y-6">
                <div class="flex flex-col items-center justify-center space-y-3">
                    <span class="sr-only" id="setup-otp-label">{{ __('OTP Code') }}</span>
                    <x-otp-input name="code" :length="6" wire-model="code" />
                </div>

                <div class="flex items-center gap-3">
                    <x-button variant="outline" class="flex-1" wire:click="resetVerification">
                        {{ __('Back') }}
                    </x-button>

                    <x-button
                        variant="primary"
                        class="flex-1"
                        wire:click="confirmTwoFactor"
                        x-bind:disabled="$wire.code.length < 6"
                    >
                        {{ __('Confirm') }}
                    </x-button>
                </div>
            </div>
        @else
            @error('setupData')
                <div class="flex items-center gap-3 rounded-xl border border-error/30 bg-error-container px-4 py-3 text-on-error-container">
                    <x-icon name="error" class="shrink-0" />
                    <p class="text-body-md">{{ $message }}</p>
                </div>
            @enderror

            <div class="flex justify-center">
                <div class="relative aspect-square w-64 overflow-hidden rounded-lg border border-outline-variant">
                    @empty($qrCodeSvg)
                        <div class="absolute inset-0 flex animate-pulse items-center justify-center bg-surface-container-low">
                            <x-icon name="progress_activity" class="text-3xl text-on-surface-variant" />
                        </div>
                    @else
                        <div class="flex h-full items-center justify-center p-4">
                            <div class="rounded bg-white p-3">
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>
                    @endempty
                </div>
            </div>

            <div>
                <x-button
                    :disabled="$errors->has('setupData')"
                    variant="primary"
                    class="w-full"
                    wire:click="showVerificationIfNecessary"
                >
                    {{ $this->modalConfig['buttonText'] }}
                </x-button>
            </div>

            <div class="space-y-4">
                <div class="relative flex w-full items-center justify-center">
                    <div class="absolute inset-0 top-1/2 h-px w-full bg-outline-variant"></div>
                    <span class="relative bg-surface-container-lowest px-2 text-sm text-on-surface-variant">
                        {{ __('or, enter the code manually') }}
                    </span>
                </div>

                <div
                    class="flex items-center gap-2"
                    x-data="{
                        copied: false,
                        async copy() {
                            try {
                                await navigator.clipboard.writeText(@js($manualSetupKey));
                                this.copied = true;
                                setTimeout(() => this.copied = false, 1500);
                            } catch (e) {
                                console.warn('Could not copy to clipboard');
                            }
                        },
                    }"
                >
                    <div class="flex w-full items-stretch rounded-xl border border-outline-variant">
                        @empty($manualSetupKey)
                            <div class="flex w-full items-center justify-center bg-surface-container-low p-3">
                                <x-icon name="progress_activity" class="text-lg text-on-surface-variant" />
                            </div>
                        @else
                            <input type="text" readonly value="{{ $manualSetupKey }}" class="w-full bg-transparent p-3 text-on-surface outline-none" />

                            <button @click="copy()" type="button" class="cursor-pointer border-s border-outline-variant px-3 transition-colors hover:bg-surface-container-low">
                                <x-icon name="content_copy" x-show="!copied" />
                                <x-icon name="check" x-show="copied" x-cloak class="text-primary" />
                            </button>
                        @endempty
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-modal>
