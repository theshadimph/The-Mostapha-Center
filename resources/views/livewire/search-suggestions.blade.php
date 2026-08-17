<?php

use App\Models\Book;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $query = '';

    /**
     * Standalone renders its own <form> (e.g. the homepage hero). Embedded
     * renders just the input + dropdown, for dropping into a caller's own
     * <form> that already handles category/submit (e.g. the search page) —
     * nesting a second <form> there would be invalid HTML.
     */
    public bool $standalone = true;

    public function mount(?string $value = null, bool $standalone = true): void
    {
        $this->query = $value ?? '';
        $this->standalone = $standalone;
    }

    /**
     * @return Collection<int, Book>
     */
    #[Computed]
    public function suggestions(): Collection
    {
        if (mb_strlen(trim($this->query)) < 2) {
            return collect();
        }

        return Book::search($this->query)
            ->query(fn ($eloquent) => $eloquent->with('author'))
            ->take(5)
            ->get();
    }
}; ?>

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    @if ($standalone)
        <form action="{{ route('search') }}" class="group relative block w-full">
            <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center">
                <x-icon name="search" class="text-2xl text-outline transition-colors group-hover:text-primary" />
            </div>
            <input
                type="text"
                name="q"
                wire:model.live.debounce.250ms="query"
                x-on:focus="open = true"
                x-on:input="open = true"
                autocomplete="off"
                placeholder="{{ __('ابحث عن كتاب أو مؤلف') }}"
                class="flex h-16 w-full items-center rounded-xl border-2 border-outline-variant bg-surface-container-lowest pe-14 ps-6 text-body-lg text-on-surface outline-none transition-all placeholder:text-on-surface-variant hover:border-primary focus:border-primary"
            >
        </form>
    @else
        <div class="relative max-w-md flex-1">
            <x-icon name="search" class="absolute right-4 top-1/2 -translate-y-1/2 text-outline" />
            <input
                type="text"
                name="q"
                wire:model.live.debounce.250ms="query"
                x-on:focus="open = true"
                x-on:input="open = true"
                autocomplete="off"
                placeholder="{{ __('ابحث عن كتاب، مؤلف، أو موضوع...') }}"
                class="h-12 w-full rounded-full border border-outline-variant bg-surface-container-high pe-4 ps-12 text-body-md outline-none transition-colors focus-within:border-primary"
            >
        </div>
    @endif

    <div
        x-show="open && $wire.query.trim().length >= 2"
        x-cloak
        x-transition
        class="absolute z-30 mt-2 max-h-96 w-full overflow-y-auto rounded-xl border border-outline-variant bg-surface-container-lowest text-right shadow-lg"
    >
        @if ($this->suggestions->isNotEmpty())
            <ul class="divide-y divide-outline-variant">
                @foreach ($this->suggestions as $book)
                    <li>
                        <a
                            href="{{ route('books.show', $book) }}"
                            wire:navigate
                            class="flex items-center gap-3 px-5 py-3 transition-colors hover:bg-surface-container-high"
                        >
                            <x-icon name="auto_stories" class="shrink-0 text-outline" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-bold text-on-surface">{{ $book->title }}</p>
                                @if ($book->author)
                                    <p class="truncate text-caption text-on-surface-variant">{{ $book->author->name }}</p>
                                @endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>

            <a
                href="{{ route('search', ['q' => $query]) }}"
                wire:navigate
                class="block border-t border-outline-variant px-5 py-3 text-center font-bold text-primary hover:bg-surface-container-high"
            >
                {{ __('عرض كل النتائج') }}
            </a>
        @else
            <p class="px-5 py-4 text-center text-body-md text-on-surface-variant">{{ __('لا توجد نتائج مطابقة') }}</p>
        @endif
    </div>
</div>
