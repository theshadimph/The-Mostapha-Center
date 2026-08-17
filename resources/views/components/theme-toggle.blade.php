{{--
    Toggles light ⇄ dark, using the same global $store.appearance as the
    settings/appearance page (resources/js/appearance.js). No account
    needed — it's a client-side, localStorage-only preference, so this is
    safe to show to anonymous visitors too.
--}}
<button
    type="button"
    x-data="{
        labels: { light: @js(__('فاتح')), dark: @js(__('داكن')) },
        icons: { light: 'light_mode', dark: 'dark_mode' },
        toggle() {
            $store.appearance?.set($store.appearance.value === 'dark' ? 'light' : 'dark');
        },
    }"
    @click="toggle()"
    :title="labels[$store.appearance?.value]"
    {{ $attributes->merge(['class' => 'rounded-full p-2 text-on-surface-variant transition-colors hover:bg-surface-container-high']) }}
>
    <span class="material-symbols-outlined" aria-hidden="true" x-text="icons[$store.appearance?.value]"></span>
    <span class="sr-only" x-text="labels[$store.appearance?.value]"></span>
</button>
