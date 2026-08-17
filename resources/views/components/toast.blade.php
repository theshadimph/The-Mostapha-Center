{{--
    Mounted once per full-page layout. Listens for the "toast-show" event
    dispatched as $this->dispatch('toast-show', variant: 'success', text: '...')
    (the replacement for Flux::toast(), which dispatches under this same
    event name — see vendor/livewire/flux/src/Concerns/InteractsWithComponents.php).
--}}
<div
    x-data="{
        toasts: [],
        push(toast) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, variant: toast.variant ?? 'success', text: toast.text ?? '' });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        },
    }"
    x-on:toast-show.window="push($event.detail)"
    class="fixed bottom-6 left-1/2 z-[200] flex w-full max-w-sm -translate-x-1/2 flex-col gap-2 px-4"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition
            @click="remove(toast.id)"
            class="flex cursor-pointer items-center gap-3 rounded-xl px-4 py-3 text-label-bold shadow-lg"
            :class="{
                'bg-primary text-on-primary': toast.variant === 'success',
                'bg-error text-on-error': toast.variant === 'danger' || toast.variant === 'error',
                'bg-surface-container-highest text-on-surface': !['success', 'danger', 'error'].includes(toast.variant),
            }"
        >
            <span x-text="toast.text"></span>
        </div>
    </template>
</div>
