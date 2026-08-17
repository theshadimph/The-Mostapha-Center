@php
    $shelf = $book->shelf;
    $section = $shelf?->librarySection;

    $neighboringShelves = $section
        ? $section->shelves()->orderBy('code')->pluck('code')
        : collect();
@endphp

<x-layouts::library :title="__('موقع الكتاب')">
    <!-- Breadcrumb -->
    <div class="mb-8 flex items-center justify-between">
        <a href="{{ route('books.show', $book) }}" wire:navigate class="flex items-center gap-2 rounded-lg px-4 py-2 text-primary transition-all hover:bg-primary-container/10">
            <x-icon name="arrow_forward" />
            <span class="font-bold">{{ __('العودة إلى الكتاب') }}</span>
        </a>
        @if ($section)
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="text-caption">{{ __('الرئيسية') }}</span>
                <x-icon name="chevron_left" class="text-xs" />
                <span class="text-caption">{{ $section->name }}</span>
                <x-icon name="chevron_left" class="text-xs" />
                <span class="text-caption font-bold text-primary">{{ __('موقع الكتاب') }}</span>
            </div>
        @endif
    </div>

    @if (! $book->isShelved())
        <section class="flex flex-col items-center rounded-xl border border-outline-variant bg-surface-container-lowest p-16 text-center">
            <x-icon name="search_off" class="mb-6 text-[80px] text-outline" />
            <h2 class="mb-2 text-headline-lg text-primary">{{ __('لم يُحدَّد موقع هذا الكتاب بعد') }}</h2>
            <p class="max-w-md text-body-md text-on-surface-variant">{{ __('لم تتم إضافة هذا الكتاب إلى أحد الأرفف بعد. يرجى مراجعة أمين المكتبة.') }}</p>
        </section>
    @else
        <!-- Content Layout -->
        <div class="grid grid-cols-1 gap-gutter lg:grid-cols-12">
            <!-- Book Summary Sidebar -->
            <div class="flex flex-col gap-gutter lg:col-span-4">
                <div class="relative flex flex-col items-center rounded-xl border border-outline-variant bg-surface-container-lowest p-6 text-center shadow-sm">
                    <!-- QR Code -->
                    <div class="group absolute left-4 top-4 flex flex-col items-center">
                        <a href="{{ route('books.qr', $book) }}" target="_blank" class="cursor-pointer rounded-lg border border-outline-variant bg-white p-2 shadow-sm transition-shadow hover:shadow-md">
                            <x-icon name="qr_code_2" class="text-4xl text-on-background" />
                        </a>
                        <p class="absolute top-full z-20 mt-2 hidden w-32 rounded bg-inverse-surface p-2 text-[10px] text-inverse-on-surface shadow-lg group-hover:block">
                            {{ __('امسح الكود ليظهر موقع الكتاب على جوالك') }}
                        </p>
                    </div>

                    <div class="mb-6 flex h-72 w-48 items-center justify-center rounded border border-outline-variant bg-surface-container-highest shadow-md">
                        @if ($book->cover_url)
                            <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                        @else
                            <x-icon name="auto_stories" class="text-5xl text-on-surface-variant/40" />
                        @endif
                    </div>

                    <h2 class="mb-2 text-headline-md text-primary">{{ $book->title }}</h2>
                    <p class="mb-4 text-body-md text-on-surface-variant">{{ $book->author?->name }}</p>

                    <div class="mb-4 h-px w-full bg-outline-variant"></div>

                    <div class="flex w-full flex-col gap-3 text-right">
                        <div class="flex items-center justify-between px-2">
                            <span class="text-caption text-on-surface-variant">{{ __('الحالة:') }}</span>
                            <x-badge :variant="$book->status->badgeVariant()">{{ $book->status->label() }}</x-badge>
                        </div>
                        <div class="flex items-center justify-between px-2">
                            <span class="text-caption text-on-surface-variant">{{ __('الرمز التعريفي:') }}</span>
                            <span class="font-bold text-primary" dir="ltr">{{ $book->barcode }}</span>
                        </div>
                    </div>
                </div>

                <!-- Instruction Steps -->
                <div class="rounded-xl bg-primary-container p-6 text-on-primary-container shadow-sm">
                    <h3 class="mb-6 flex items-center gap-2 font-bold">
                        <x-icon name="info" />
                        {{ __('خطوات الوصول للكتاب') }}
                    </h3>
                    <div class="space-y-6">
                        @foreach ([
                            __('توجه إلى :section', ['section' => $section->name]),
                            __('ابحث عن رقم الرف (:shelf)', ['shelf' => $shelf->code]),
                            __('خذ الكتاب وتأكد من بياناته'),
                            __('أعد الكتاب إلى نفس المكان بدقة'),
                        ] as $index => $step)
                            <div class="flex gap-4">
                                <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-on-primary-container font-bold text-primary-container">{{ $index + 1 }}</div>
                                <p class="text-body-md">{{ $step }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Main Location Content -->
            <div class="flex flex-col gap-gutter lg:col-span-8">
                <!-- Location Info Grid -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 text-center">
                        <x-icon name="account_balance" class="mb-2 text-primary" />
                        <p class="mb-1 block text-caption text-on-surface-variant">{{ __('القاعة') }}</p>
                        <p class="font-bold text-primary">{{ $section->name }}</p>
                    </div>
                    <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 text-center">
                        <x-icon name="layers" class="mb-2 text-primary" />
                        <p class="mb-1 block text-caption text-on-surface-variant">{{ __('الطابق') }}</p>
                        <p class="font-bold text-primary">{{ $section->floor ?? __('غير محدد') }}</p>
                    </div>
                    <div class="rounded-xl border-2 border-primary bg-surface-container-lowest p-5 text-center shadow-md">
                        <x-icon name="grid_view" fill class="mb-2 text-primary" />
                        <p class="mb-1 block text-caption text-on-surface-variant">{{ __('الرف') }}</p>
                        <p class="font-bold text-primary">{{ $shelf->code }}</p>
                    </div>
                    <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 text-center">
                        <x-icon name="tag" class="mb-2 text-primary" />
                        <p class="mb-1 block text-caption text-on-surface-variant">{{ __('الموقع') }}</p>
                        <p class="font-bold text-primary" dir="ltr">{{ $book->location_code }}</p>
                    </div>
                </div>

                <!-- Visual Schematic Map -->
                @if ($neighboringShelves->isNotEmpty())
                    <div class="relative flex min-h-[450px] flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest p-8 shadow-sm">
                        <div class="mb-8 flex items-center justify-between">
                            <h3 class="text-headline-md text-primary">{{ __('مخطط موقع الرف') }}</h3>
                            <div class="flex items-center gap-6">
                                <div class="flex items-center gap-2">
                                    <div class="size-4 rounded-full bg-primary"></div>
                                    <span class="text-caption text-on-surface-variant">{{ __('موقع الكتاب') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="size-4 rounded-full bg-secondary-container"></div>
                                    <span class="text-caption text-on-surface-variant">{{ __('أرفف أخرى') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="relative flex flex-grow items-center justify-center rounded-xl border border-outline-variant bg-surface-container-low p-12">
                            <div class="grid w-full max-w-2xl grid-cols-5 items-end gap-8">
                                @foreach ($neighboringShelves as $shelfCode)
                                    @if ($shelfCode === $shelf->code)
                                        <div class="relative z-10 flex h-56 items-center justify-center rounded bg-primary shadow-2xl">
                                            <span class="text-xl font-bold text-on-primary">{{ $shelfCode }}</span>
                                            <div class="absolute -top-14 flex flex-col items-center">
                                                <x-icon name="location_on" fill class="text-5xl text-secondary-container" />
                                                <div class="mt-[-8px] size-2 rounded-full bg-secondary-container"></div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex h-40 items-end justify-center rounded bg-secondary-container pb-4 shadow-sm transition-all hover:scale-105">
                                            <span class="text-[10px] font-bold text-on-secondary-container">{{ $shelfCode }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-8 flex items-center gap-4 rounded-lg border-e-4 border-primary bg-primary/5 p-4">
                            <x-icon name="lightbulb" class="text-primary" />
                            <p class="text-body-md text-on-surface-variant">
                                {{ __('نصيحة: الرف :shelf يقع ضمن :section.', ['shelf' => $shelf->code, 'section' => $section->name]) }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Footer Actions -->
                <div class="mt-4 flex flex-col items-center gap-4 sm:flex-row">
                    <a href="{{ route('search') }}" wire:navigate class="w-full sm:flex-1">
                        <x-button variant="primary" class="w-full !py-4">{{ __('العودة للبحث') }}</x-button>
                    </a>
                    <x-button variant="outline" class="w-full sm:w-auto" onclick="window.print()">
                        <x-icon name="print" />
                        {{ __('طباعة الإحداثيات') }}
                    </x-button>
                </div>
            </div>
        </div>
    @endif
</x-layouts::library>
