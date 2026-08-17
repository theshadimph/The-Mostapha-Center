<x-layouts::library :title="__('الرئيسية')">
    <!-- Hero Section & Search -->
    <div class="mx-auto mb-16 w-full max-w-4xl text-center">
        <h2 class="mb-8 text-headline-xl tracking-tight text-primary">{{ __('ابحث في كنوز المعرفة') }}</h2>

        <livewire:search-suggestions />

        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('search') }}" wire:navigate class="rounded-full border border-outline-variant bg-surface-container-lowest px-5 py-2.5 text-label-bold font-bold text-on-surface-variant transition-all hover:border-transparent hover:bg-secondary-container hover:text-on-secondary-container">{{ __('اسم الكتاب') }}</a>
            <a href="{{ route('search') }}" wire:navigate class="rounded-full border border-outline-variant bg-surface-container-lowest px-5 py-2.5 text-label-bold font-bold text-on-surface-variant transition-all hover:border-transparent hover:bg-secondary-container hover:text-on-secondary-container">{{ __('اسم المؤلف') }}</a>
            <a href="{{ route('search') }}" wire:navigate class="rounded-full border border-outline-variant bg-surface-container-lowest px-5 py-2.5 text-label-bold font-bold text-on-surface-variant transition-all hover:border-transparent hover:bg-secondary-container hover:text-on-secondary-container">{{ __('التصنيف') }}</a>
            <a href="{{ route('search') }}" wire:navigate class="rounded-full border border-outline-variant bg-surface-container-lowest px-5 py-2.5 text-label-bold font-bold text-on-surface-variant transition-all hover:border-transparent hover:bg-secondary-container hover:text-on-secondary-container">{{ __('الكلمات المفتاحية') }}</a>
        </div>
    </div>

    <!-- Category Bento Grid -->
    @if ($categories->isNotEmpty())
        <section class="mb-20">
            <div class="mb-8 flex items-center gap-3 border-e-4 border-primary pe-4">
                <h3 class="text-headline-md text-primary">{{ __('الأقسام العلمية') }}</h3>
            </div>

            <div class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-6">
                @foreach ($categories as $item)
                    <a href="{{ route('search', ['category' => $item['category']->slug]) }}" wire:navigate class="group flex flex-col items-center gap-4 rounded-xl bg-surface-container-lowest p-6 text-center shadow-sm transition-all duration-300 hover:bg-primary hover:-translate-y-1">
                        <div class="flex size-16 items-center justify-center rounded-full bg-primary-fixed transition-colors group-hover:bg-primary-container">
                            <x-icon :name="$item['icon']" class="text-3xl text-primary group-hover:text-white" />
                        </div>
                        <span class="text-label-bold font-bold text-on-surface group-hover:text-white">{{ $item['category']->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="grid w-full grid-cols-1 gap-12 lg:grid-cols-12">
        <!-- Popular Books -->
        <div class="lg:col-span-8">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-headline-md text-primary">{{ __('الكتب الأكثر طلباً') }}</h3>
                <a href="{{ route('search') }}" wire:navigate class="font-bold text-primary transition-all hover:underline">{{ __('عرض الكل') }}</a>
            </div>

            @if ($popularBooks->isNotEmpty())
                <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                    @foreach ($popularBooks as $book)
                        <a href="{{ route('books.show', $book) }}" wire:navigate class="group cursor-pointer">
                            <div class="relative mb-3 aspect-[3/4] overflow-hidden rounded-xl bg-primary-fixed shadow-sm transition-all duration-300 group-hover:-translate-y-2 group-hover:shadow-lg">
                                @if ($book->cover_url)
                                    <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <x-icon name="auto_stories" class="text-5xl text-primary/40" />
                                    </div>
                                @endif
                                <div class="absolute top-2 right-2 rounded bg-primary px-2 py-1 text-xs font-bold text-white">{{ $book->status->label() }}</div>
                            </div>
                            <h4 class="line-clamp-1 font-bold text-on-surface transition-colors group-hover:text-primary">{{ $book->title }}</h4>
                            <p class="text-caption text-on-surface-variant">{{ $book->author?->name ?? __('مؤلف غير معروف') }}</p>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-body-md text-on-surface-variant">{{ __('لا توجد كتب متاحة حالياً.') }}</p>
            @endif
        </div>

        <!-- Recently Searched -->
        <div class="h-fit rounded-xl border border-outline-variant bg-surface-container-low p-6 lg:col-span-4">
            <div class="mb-6 flex items-center gap-2 text-primary">
                <x-icon name="history" />
                <h3 class="text-headline-md">{{ __('التي بحثت عنها مؤخراً') }}</h3>
            </div>

            @if ($recentlySearched->isNotEmpty())
                <ul class="space-y-4">
                    @foreach ($recentlySearched as $book)
                        <li class="{{ ! $loop->first ? 'border-t border-outline-variant pt-4' : '' }}">
                            <a href="{{ route('books.show', $book) }}" wire:navigate class="group flex items-center gap-4 rounded-xl p-3 transition-all hover:bg-surface-container-high">
                                <div class="flex h-16 w-12 shrink-0 items-center justify-center rounded-lg bg-outline-variant/40 shadow-sm">
                                    <x-icon name="menu_book" class="text-on-surface-variant" />
                                </div>
                                <div class="flex-grow">
                                    <p class="font-bold text-on-surface transition-colors group-hover:text-primary">{{ $book->title }}</p>
                                    <p class="text-caption text-on-surface-variant">{{ $book->author?->name ?? __('مؤلف غير معروف') }}</p>
                                </div>
                                <x-icon name="chevron_left" class="text-outline-variant transition-all group-hover:-translate-x-1 group-hover:text-primary" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-body-md text-on-surface-variant">{{ __('لم تتصفح أي كتب بعد.') }}</p>
            @endif
        </div>
    </div>
</x-layouts::library>
