@props([
    'name' => 'code',
    'length' => 6,
    'wireModel' => null,
])

<div
    x-data="{
        length: {{ (int) $length }},
        digits: Array.from({ length: {{ (int) $length }} }, () => ''),
        get code() { return this.digits.join(''); },
        onInput(i, e) {
            const val = e.target.value.replace(/\D/g, '').slice(-1);
            this.digits[i] = val;
            e.target.value = val;
            if (val && e.target.nextElementSibling?.matches('input')) {
                e.target.nextElementSibling.focus();
            }
        },
        onKeydown(i, e) {
            if (e.key === 'Backspace' && !this.digits[i] && e.target.previousElementSibling?.matches('input')) {
                e.target.previousElementSibling.focus();
            }
        },
        onPaste(e) {
            e.preventDefault();
            const pasted = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, this.length).split('');
            pasted.forEach((d, i) => { this.digits[i] = d; });
            this.$nextTick(() => {
                const boxes = this.$el.querySelectorAll('input[type=text]');
                boxes[Math.min(pasted.length, this.length) - 1]?.focus();
            });
        },
    }"
    x-init="$nextTick(() => $el.querySelector('input')?.focus())"
    @if ($wireModel)
        x-effect="$wire.set(@js($wireModel), code)"
    @endif
    class="flex items-center justify-center gap-2"
    dir="ltr"
>
    <template x-for="(digit, i) in digits" :key="i">
        <input
            type="text"
            inputmode="numeric"
            maxlength="1"
            autocomplete="one-time-code"
            :value="digit"
            @input="onInput(i, $event)"
            @keydown="onKeydown(i, $event)"
            @paste="onPaste($event)"
            class="h-14 w-12 rounded-lg border border-outline-variant bg-surface-container-lowest text-center text-headline-md font-bold outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20"
        />
    </template>

    <input type="hidden" name="{{ $name }}" :value="code" />
</div>
