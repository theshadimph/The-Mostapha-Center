@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('التنقل بين الصفحات') }}" class="flex flex-col items-center justify-between gap-4 sm:flex-row">
        <p class="text-caption text-on-surface-variant">
            {{ __('عرض :from إلى :to من أصل :total نتيجة', [
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
            ]) }}
        </p>

        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="flex size-10 items-center justify-center rounded-lg text-on-surface-variant/40">
                    <x-icon name="chevron_right" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" wire:navigate class="flex size-10 items-center justify-center rounded-lg text-primary transition-colors hover:bg-surface-container-high">
                    <x-icon name="chevron_right" />
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-on-surface-variant">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="flex size-10 items-center justify-center rounded-lg bg-primary text-label-md font-bold text-on-primary">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" wire:navigate class="flex size-10 items-center justify-center rounded-lg text-label-md font-bold text-on-surface-variant transition-colors hover:bg-surface-container-high" aria-label="{{ __('الانتقال إلى الصفحة :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" wire:navigate class="flex size-10 items-center justify-center rounded-lg text-primary transition-colors hover:bg-surface-container-high">
                    <x-icon name="chevron_left" />
                </a>
            @else
                <span class="flex size-10 items-center justify-center rounded-lg text-on-surface-variant/40">
                    <x-icon name="chevron_left" />
                </span>
            @endif
        </div>
    </nav>
@endif
