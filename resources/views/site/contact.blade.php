@php
    $contact = config('app.contact');
    $hasContactDetails = collect($contact)->except('map_url')->filter()->isNotEmpty();
@endphp

<x-layouts::library :title="__('تواصل معنا')">
    <section class="mx-auto mb-16 max-w-4xl text-center">
        <h1 class="mb-6 text-headline-xl text-primary">{{ __('تواصل معنا') }}</h1>
        <p class="text-body-lg leading-relaxed text-on-surface-variant">
            {{ __('يسعدنا تواصلكم معنا لأي استفسار عن المركز أو برامجه أو مكتبته العلمية، عبر النموذج أدناه أو من خلال وسائل التواصل المتاحة.') }}
        </p>
    </section>

    <div class="grid grid-cols-1 gap-gutter lg:grid-cols-12">
        <!-- Contact form -->
        <div class="lg:col-span-7">
            <div class="rounded-2xl border border-outline-variant bg-surface-container-lowest p-8 shadow-sm">
                @if (session('status'))
                    <div class="mb-6 flex items-center gap-3 rounded-xl bg-tertiary-fixed px-4 py-3 text-on-tertiary-fixed-variant">
                        <x-icon name="check_circle" fill />
                        <span class="font-bold">{{ session('status') }}</span>
                    </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="flex flex-col gap-4">
                    @csrf

                    <x-input name="name" :label="__('الاسم')" :value="old('name')" required />
                    <x-input name="email" type="email" :label="__('البريد الإلكتروني')" :value="old('email')" required dir="ltr" />

                    <div>
                        <label class="mb-1.5 block text-label-bold font-bold text-primary">{{ __('الرسالة') }}</label>
                        <textarea name="message" rows="5" required class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-body-md outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-caption text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-button type="submit" variant="primary" class="mt-2">
                        <x-icon name="send" />
                        {{ __('إرسال الرسالة') }}
                    </x-button>
                </form>
            </div>
        </div>

        <!-- Contact details -->
        <div class="flex flex-col gap-4 lg:col-span-5">
            @if ($hasContactDetails)
                <div class="flex flex-col gap-5 rounded-2xl border border-outline-variant bg-surface-container-lowest p-8 shadow-sm">
                    @if ($contact['address'])
                        <div class="flex items-start gap-3">
                            <x-icon name="location_on" class="mt-0.5 text-primary" />
                            <span class="text-body-md text-on-surface">{{ $contact['address'] }}</span>
                        </div>
                    @endif
                    @if ($contact['phone'])
                        <div class="flex items-start gap-3">
                            <x-icon name="call" class="mt-0.5 text-primary" />
                            <span class="text-body-md text-on-surface" dir="ltr">{{ $contact['phone'] }}</span>
                        </div>
                    @endif
                    @if ($contact['whatsapp'])
                        <div class="flex items-start gap-3">
                            <x-icon name="chat" class="mt-0.5 text-primary" />
                            <span class="text-body-md text-on-surface" dir="ltr">{{ $contact['whatsapp'] }}</span>
                        </div>
                    @endif
                    @if ($contact['email'])
                        <div class="flex items-start gap-3">
                            <x-icon name="mail" class="mt-0.5 text-primary" />
                            <span class="text-body-md text-on-surface" dir="ltr">{{ $contact['email'] }}</span>
                        </div>
                    @endif
                </div>
            @endif

            @if ($contact['map_url'])
                <div class="overflow-hidden rounded-2xl border border-outline-variant shadow-sm">
                    <iframe src="{{ $contact['map_url'] }}" class="h-72 w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            @endif
        </div>
    </div>
</x-layouts::library>
