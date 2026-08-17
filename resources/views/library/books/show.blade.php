@php
    $citations = [
        'apa' => __(':author. (:year). :title (ط. :edition). :publisher.', [
            'author' => $book->author?->name ?? __('مؤلف غير معروف'),
            'year' => $book->publication_year ?? __('د.ت'),
            'title' => $book->title,
            'edition' => $book->edition ?? '1',
            'publisher' => $book->publisher ?? __('د.ن'),
        ]),
        'chicago' => __(':author. :title. :publisher، :year.', [
            'author' => $book->author?->name ?? __('مؤلف غير معروف'),
            'title' => $book->title,
            'publisher' => $book->publisher ?? __('د.ن'),
            'year' => $book->publication_year ?? __('د.ت'),
        ]),
        'mla' => __(':author. :title. :publisher، :year.', [
            'author' => $book->author?->name ?? __('مؤلف غير معروف'),
            'title' => $book->title,
            'publisher' => $book->publisher ?? __('د.ن'),
            'year' => $book->publication_year ?? __('د.ت'),
        ]),
    ];

    $languageLabels = ['ar' => __('العربية'), 'en' => __('الإنجليزية')];
@endphp

<x-layouts::library :title="$book->title">
    <!-- Breadcrumbs -->
    <nav class="mb-10 flex items-center gap-2 text-on-surface-variant">
        <a href="{{ route('search') }}" wire:navigate class="font-bold transition-colors hover:text-primary">{{ __('المكتبة') }}</a>
        @if ($book->category)
            <x-icon name="chevron_left" class="text-sm" />
            <a href="{{ route('search', ['category' => $book->category->slug]) }}" wire:navigate class="font-bold transition-colors hover:text-primary">{{ $book->category->name }}</a>
        @endif
        <x-icon name="chevron_left" class="text-sm" />
        <span class="font-bold text-secondary">{{ $book->title }}</span>
    </nav>

    <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
        <!-- Left Column: Media & Actions -->
        <div class="flex flex-col gap-8 lg:col-span-4">
            <div class="relative aspect-[3/4] w-full overflow-hidden rounded-xl border border-outline-variant bg-surface-container-highest">
                <div class="flex h-full w-full items-center justify-center">
                    @if ($book->cover_url)
                        <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                    @else
                        <x-icon name="auto_stories" class="text-6xl text-on-surface-variant/40" />
                    @endif
                </div>
                <div class="absolute top-4 right-4 flex items-center gap-1 rounded-full bg-primary px-3 py-1 text-[12px] font-bold text-on-primary shadow-md">
                    <span class="size-2 rounded-full bg-primary-fixed"></span>
                    {{ $book->status->label() }}
                </div>
            </div>

            <div class="flex flex-col gap-3">
                @if ($book->status->isPubliclyAvailable())
                    <a href="{{ route('books.location', $book) }}" wire:navigate>
                        <x-button variant="secondary" class="w-full">
                            <x-icon name="location_on" />
                            {{ __('عرض الموقع في المكتبة') }}
                        </x-button>
                    </a>
                @endif
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="document.getElementById('citation-widget').scrollIntoView({behavior: 'smooth'})" class="flex items-center justify-center gap-2 rounded-xl border-2 border-outline-variant py-3 font-bold text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary">
                        <x-icon name="format_quote" class="text-sm" />
                        {{ __('اقتباس') }}
                    </button>
                    <a href="{{ route('books.qr', $book) }}" target="_blank" class="flex items-center justify-center gap-2 rounded-xl border-2 border-outline-variant py-3 font-bold text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-primary">
                        <x-icon name="qr_code_2" class="text-sm" />
                        {{ __('رمز QR') }}
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="flex flex-col gap-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-6">
                @if ($book->publisher)
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-on-surface-variant">{{ __('الناشر') }}</span>
                        <span class="text-[18px] text-primary">{{ $book->publisher }}</span>
                    </div>
                    <div class="h-px w-full bg-outline-variant/50"></div>
                @endif
                @if ($book->publication_year)
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-on-surface-variant">{{ __('سنة النشر') }}</span>
                        <span class="text-[20px] text-primary">{{ $book->publication_year }}</span>
                    </div>
                    <div class="h-px w-full bg-outline-variant/50"></div>
                @endif
                @if ($book->edition)
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-on-surface-variant">{{ __('الطبعة') }}</span>
                        <span class="text-[18px] text-primary">{{ $book->edition }}</span>
                    </div>
                    <div class="h-px w-full bg-outline-variant/50"></div>
                @endif
                @if ($book->pages)
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-on-surface-variant">{{ __('عدد الصفحات') }}</span>
                        <span class="text-[20px] font-bold text-primary">{{ $book->pages }}</span>
                    </div>
                    <div class="h-px w-full bg-outline-variant/50"></div>
                @endif
                @if ($book->isbn)
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-on-surface-variant">{{ __('رقم الردمك (ISBN)') }}</span>
                        <span class="text-[16px] text-primary" dir="ltr">{{ $book->isbn }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Info & Description -->
        <div class="flex flex-col gap-8 lg:col-span-8">
            <header class="flex flex-col gap-4">
                <h1 class="text-headline-xl leading-tight text-primary">{{ $book->title }}</h1>
                @if ($book->author)
                    <span class="text-[24px] font-medium text-secondary">{{ __('لـ :author', ['author' => $book->author->name]) }}</span>
                @endif
            </header>

            <!-- Metadata Grid -->
            <div class="grid grid-cols-2 gap-x-6 gap-y-8 border-y border-outline-variant/50 py-8 md:grid-cols-3">
                @if ($book->author)
                    <div class="flex flex-col gap-2">
                        <span class="text-caption font-bold uppercase tracking-wider text-on-surface-variant">{{ __('المؤلف') }}</span>
                        <span class="font-medium text-primary">{{ $book->author->name }}</span>
                    </div>
                @endif
                <div class="flex flex-col gap-2">
                    <span class="text-caption font-bold uppercase tracking-wider text-on-surface-variant">{{ __('لغة الكتاب') }}</span>
                    <span class="font-medium text-primary">{{ $languageLabels[$book->language] ?? $book->language }}</span>
                </div>
                @if ($book->category)
                    <div class="col-span-2 flex flex-col gap-2 md:col-span-1">
                        <span class="text-caption font-bold uppercase tracking-wider text-on-surface-variant">{{ __('القسم العلمي') }}</span>
                        <div class="mt-1 flex flex-wrap gap-2">
                            <x-badge>{{ $book->category->name }}</x-badge>
                        </div>
                    </div>
                @endif
                @if (! empty($book->editors))
                    <div class="col-span-2 flex flex-col gap-2 md:col-span-3">
                        <span class="text-caption font-bold uppercase tracking-wider text-on-surface-variant">{{ __('المحققون') }}</span>
                        <div class="mt-1 flex flex-wrap gap-2">
                            @foreach ($book->editors as $editor)
                                <x-badge>{{ $editor }}</x-badge>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (! empty($book->commentators))
                    <div class="col-span-2 flex flex-col gap-2 md:col-span-3">
                        <span class="text-caption font-bold uppercase tracking-wider text-on-surface-variant">{{ __('الشارحون') }}</span>
                        <div class="mt-1 flex flex-wrap gap-2">
                            @foreach ($book->commentators as $commentator)
                                <x-badge>{{ $commentator }}</x-badge>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (! empty($book->annotators))
                    <div class="col-span-2 flex flex-col gap-2 md:col-span-3">
                        <span class="text-caption font-bold uppercase tracking-wider text-on-surface-variant">{{ __('المحشّون') }}</span>
                        <div class="mt-1 flex flex-wrap gap-2">
                            @foreach ($book->annotators as $annotator)
                                <x-badge>{{ $annotator }}</x-badge>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Description -->
            @if ($book->description)
                <section class="flex flex-col gap-6">
                    <h2 class="flex items-center gap-3 text-headline-lg text-primary">
                        <span class="h-8 w-1.5 rounded-full bg-secondary-container"></span>
                        {{ __('نبذة عن الكتاب') }}
                    </h2>
                    <div class="space-y-5 text-body-lg leading-relaxed text-on-surface">
                        <p>{{ $book->description }}</p>
                    </div>
                </section>
            @endif

            @if ($relatedBooks->isNotEmpty())
                <section class="flex flex-col gap-6">
                    <h2 class="text-headline-lg text-primary">{{ __('كتب ذات صلة') }}</h2>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        @foreach ($relatedBooks as $related)
                            <a href="{{ route('books.show', $related) }}" wire:navigate class="group flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm transition-all hover:shadow-lg">
                                <div class="flex h-32 items-center justify-center bg-surface-container-highest">
                                    <x-icon name="auto_stories" class="text-3xl text-on-surface-variant/50" />
                                </div>
                                <div class="p-3">
                                    <h4 class="line-clamp-1 text-body-md font-bold text-on-surface group-hover:text-primary">{{ $related->title }}</h4>
                                    <p class="text-caption text-on-surface-variant">{{ $related->author?->name }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>

    <!-- Citation Generator Widget -->
    <div class="mt-20 grid grid-cols-1 lg:grid-cols-12">
        <section class="lg:col-span-5 lg:col-start-8" id="citation-widget">
            <div
                x-data="{
                    active: 'apa',
                    copied: false,
                    citations: @js($citations),
                    async copy() {
                        await navigator.clipboard.writeText(this.citations[this.active]);
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    },
                }"
                class="flex flex-col gap-6 rounded-2xl bg-primary-container p-8 text-on-primary-container shadow-lg"
            >
                <div class="flex items-center gap-3">
                    <x-icon name="format_quote" class="text-3xl" />
                    <h3 class="font-bold">{{ __('مولد الاقتباسات العلمية') }}</h3>
                </div>
                <p class="leading-relaxed opacity-90">{{ __('انسخ التوثيق العلمي للكتاب حسب المعايير الأكاديمية المعتمدة.') }}</p>

                <div class="flex flex-col gap-5">
                    <div class="flex gap-2 rounded-xl bg-on-primary-container/10 p-1">
                        <template x-for="style in ['apa', 'chicago', 'mla']" :key="style">
                            <button
                                type="button"
                                @click="active = style"
                                :class="active === style ? 'bg-on-primary-container text-primary-container shadow-sm' : 'hover:bg-on-primary-container/20'"
                                class="flex-1 rounded-lg py-2 font-bold uppercase transition-all"
                                x-text="style"
                            ></button>
                        </template>
                    </div>

                    <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-5 text-on-surface shadow-inner">
                        <p class="text-right leading-relaxed" x-text="citations[active]"></p>
                    </div>

                    <button
                        type="button"
                        @click="copy()"
                        class="flex transform items-center justify-center gap-2 rounded-xl bg-secondary-container py-4 font-bold text-on-secondary-container shadow-md transition-colors hover:-translate-y-0.5 hover:bg-secondary-fixed-dim hover:shadow-lg"
                    >
                        <x-icon name="content_copy" x-show="!copied" class="text-sm" />
                        <x-icon name="check" x-show="copied" x-cloak class="text-sm" />
                        <span x-text="copied ? @js(__('تم النسخ بنجاح')) : @js(__('نسخ التوثيق'))"></span>
                    </button>
                </div>
            </div>
        </section>
    </div>
</x-layouts::library>
