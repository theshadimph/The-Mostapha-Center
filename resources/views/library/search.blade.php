<x-layouts::library :title="__('نتائج البحث')">
    @if ($results->isNotEmpty())
        <!-- Search & Filter Section -->
        <section class="mb-10">
            <div class="flex flex-col gap-6">
                <div>
                    <h2 class="mb-2 text-headline-lg text-primary">{{ __('نتائج البحث') }}</h2>
                    <p class="text-body-md text-on-surface-variant">
                        {{ __('تم العثور على :count نتيجة', ['count' => $results->total()]) }}
                        @if ($query !== '')
                            {{ __('لكلمة ":query"', ['query' => $query]) }}
                        @endif
                    </p>
                </div>

                <form action="{{ route('search') }}" class="flex flex-wrap items-center gap-3">
                    <div class="max-w-md flex-1">
                        <livewire:search-suggestions :value="$query" :standalone="false" />
                    </div>

                    <select name="category" class="h-12 rounded-full border border-outline-variant bg-surface-container-high px-4 text-body-md outline-none transition-colors focus:border-primary" onchange="this.form.submit()">
                        <option value="">{{ __('كل الأقسام') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected($activeCategory === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>

                    <x-button type="submit" variant="primary" size="sm">{{ __('بحث') }}</x-button>
                    @if ($query !== '' || $activeCategory)
                        <a href="{{ route('search') }}" wire:navigate class="font-bold text-primary hover:underline">{{ __('مسح الكل') }}</a>
                    @endif
                </form>
            </div>
        </section>

        <!-- Bento Grid / Cards Layout -->
        <div class="grid grid-cols-1 gap-gutter md:grid-cols-2 xl:grid-cols-3">
            @foreach ($results as $book)
                <div class="group flex gap-5 rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <a href="{{ route('books.show', $book) }}" wire:navigate class="flex h-44 w-32 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-surface-container-highest shadow-sm">
                        @if ($book->cover_url)
                            <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                        @else
                            <x-icon name="auto_stories" class="text-4xl text-on-surface-variant/50" />
                        @endif
                    </a>

                    <div class="flex flex-grow flex-col justify-between">
                        <div>
                            <div class="mb-2 flex items-start justify-between">
                                @if ($book->category)
                                    <x-badge variant="primary">{{ $book->category->name }}</x-badge>
                                @endif
                            </div>
                            <a href="{{ route('books.show', $book) }}" wire:navigate>
                                <h3 class="mb-1 text-headline-md leading-tight text-on-background transition-colors group-hover:text-primary">{{ $book->title }}</h3>
                            </a>
                            <p class="font-medium text-body-md text-on-surface-variant">{{ $book->author?->name ?? __('مؤلف غير معروف') }}</p>
                        </div>

                        <div class="mt-4 flex flex-col gap-3">
                            @if ($book->status->isPubliclyAvailable())
                                <div class="flex items-center gap-1.5 text-primary">
                                    <x-icon name="check_circle" fill class="text-[18px]" />
                                    <span class="text-label-md font-bold">{{ $book->status->label() }} ✓</span>
                                </div>
                                <a href="{{ route('books.location', $book) }}" wire:navigate>
                                    <x-button variant="primary" class="w-full">
                                        <x-icon name="location_on" class="text-[20px]" />
                                        {{ __('عرض الموقع') }}
                                    </x-button>
                                </a>
                            @else
                                <div class="flex items-center gap-1.5 text-error">
                                    <x-icon name="cancel" class="text-[18px]" />
                                    <span class="text-label-md font-bold">{{ $book->status->label() }} ✗</span>
                                </div>
                                <x-button variant="outline" class="w-full" disabled>
                                    <x-icon name="notifications_active" class="text-[20px]" />
                                    {{ __('أبلغني عند التوفر') }}
                                </x-button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if ($results->hasPages())
            <div class="mt-12 flex items-center justify-center gap-2">
                <a
                    href="{{ $results->previousPageUrl() ?? '#' }}"
                    wire:navigate
                    class="rounded-lg p-2 text-primary transition-colors hover:bg-surface-container-high {{ $results->onFirstPage() ? 'pointer-events-none opacity-30' : '' }}"
                >
                    <x-icon name="chevron_right" />
                </a>

                @foreach (range(max(1, $results->currentPage() - 2), min($results->lastPage(), $results->currentPage() + 2)) as $page)
                    <a
                        href="{{ $results->url($page) }}"
                        wire:navigate
                        class="flex size-10 items-center justify-center rounded-lg text-label-md font-bold transition-colors {{ $page === $results->currentPage() ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high' }}"
                    >
                        {{ $page }}
                    </a>
                @endforeach

                <a
                    href="{{ $results->nextPageUrl() ?? '#' }}"
                    wire:navigate
                    class="rounded-lg p-2 text-primary transition-colors hover:bg-surface-container-high {{ ! $results->hasMorePages() ? 'pointer-events-none opacity-30' : '' }}"
                >
                    <x-icon name="chevron_left" />
                </a>
            </div>
        @endif
    @else
        <!-- Empty State -->
        <section class="mb-20 flex flex-col items-center text-center">
            <div class="relative mb-12 flex size-64 items-center justify-center md:size-80">
                <div class="absolute inset-0 rounded-full bg-primary-fixed-dim/20"></div>
                <div class="absolute inset-4 animate-[spin_20s_linear_infinite] rounded-full border-2 border-dashed border-primary"></div>
                <x-icon name="search_off" class="text-[120px] text-primary" />
            </div>

            <h2 class="mb-4 text-headline-xl text-primary">{{ __('لم نجد ما تبحث عنه بالضبط') }}</h2>
            <p class="mx-auto mb-10 max-w-2xl text-body-lg text-on-surface-variant">
                @if ($query !== '')
                    {{ __('بحثنا في كل الزوايا ولم نعثر على نتيجة مطابقة لـ') }}
                    <span class="font-bold italic text-primary">"{{ $query }}"</span>.
                @endif
                {{ __('لا تقلق، قد تكون هذه بداية لرحلة اكتشاف جديدة.') }}
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('home') }}" wire:navigate>
                    <x-button variant="outline" size="lg">
                        <x-icon name="home" />
                        {{ __('العودة للرئيسية') }}
                    </x-button>
                </a>
            </div>
        </section>

        @if ($suggestions->isNotEmpty())
            <!-- Suggested Alternatives -->
            <section class="mb-20">
                <div class="mb-8 flex items-end justify-between">
                    <div>
                        <h3 class="text-headline-md text-primary">{{ __('قد ينال إعجابك') }}</h3>
                        <p class="text-body-md text-on-surface-variant">{{ __('اقتراحات مختارة بناءً على اهتمامات القراء') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    @foreach ($suggestions as $book)
                        <a href="{{ route('books.show', $book) }}" wire:navigate class="group flex flex-col overflow-hidden rounded-xl border-t-4 border-primary bg-surface-container-lowest shadow-sm transition-all duration-300 hover:shadow-xl">
                            <div class="flex h-40 items-center justify-center bg-surface-container-highest">
                                <x-icon name="auto_stories" class="text-4xl text-on-surface-variant/50" />
                            </div>
                            <div class="p-5">
                                <h4 class="mb-2 text-[18px] font-bold text-on-surface transition-colors group-hover:text-primary">{{ $book->title }}</h4>
                                <p class="text-caption font-bold text-primary">{{ $book->author?->name ?? __('مؤلف غير معروف') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Search Tips -->
        <section class="mx-auto max-w-4xl rounded-2xl border-e-8 border-secondary bg-surface-container-low p-10">
            <div class="flex flex-col items-center gap-10 md:flex-row">
                <x-icon name="lightbulb" class="shrink-0 text-[80px] text-secondary" />
                <div>
                    <h3 class="mb-4 text-headline-md text-primary">{{ __('نصائح لبحث أفضل') }}</h3>
                    <ul class="space-y-3 text-body-md text-on-surface-variant">
                        <li class="flex items-center gap-3"><span class="size-2 rounded-full bg-secondary"></span>{{ __('تأكد من صحة إملاء الكلمات التي تبحث عنها.') }}</li>
                        <li class="flex items-center gap-3"><span class="size-2 rounded-full bg-secondary"></span>{{ __('استخدم كلمات مفتاحية أكثر عمومية بدلاً من العناوين الطويلة.') }}</li>
                        <li class="flex items-center gap-3"><span class="size-2 rounded-full bg-secondary"></span>{{ __('جرب البحث باسم المؤلف إذا كنت لا تتذكر عنوان الكتاب بدقة.') }}</li>
                    </ul>
                </div>
            </div>
        </section>
    @endif
</x-layouts::library>
