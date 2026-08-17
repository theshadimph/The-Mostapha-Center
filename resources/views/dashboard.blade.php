<x-layouts::app :title="__('لوحة التحكم')">
    <div class="flex w-full flex-1 flex-col gap-gutter">
        <!-- Stat cards -->
        <section class="grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-4">
            <div class="flex flex-col rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-label-sm text-on-surface-variant">{{ __('إجمالي الكتب') }}</p>
                    <x-icon name="menu_book" class="rounded-lg bg-primary/10 p-2 text-primary" />
                </div>
                <h3 class="mb-1 text-headline-lg text-primary">{{ number_format($totalBooks) }}</h3>
            </div>

            <div class="flex flex-col rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-label-sm text-on-surface-variant">{{ __('الكتب المتوفرة') }}</p>
                    <x-icon name="check_circle" class="rounded-lg bg-tertiary/10 p-2 text-tertiary" />
                </div>
                <h3 class="mb-1 text-headline-lg text-tertiary">{{ number_format($availableBooks) }}</h3>
            </div>

            <div class="flex flex-col rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-label-sm text-on-surface-variant">{{ __('الكتب المستعارة') }}</p>
                    <x-icon name="hourglass_empty" class="rounded-lg bg-secondary/10 p-2 text-secondary" />
                </div>
                <h3 class="mb-1 text-headline-lg text-secondary">{{ number_format($borrowedBooks) }}</h3>
            </div>

            <div class="flex flex-col rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-label-sm text-on-surface-variant">{{ __('كتب غير موزعة على الأرفف') }}</p>
                    <x-icon name="warning" class="rounded-lg bg-error/10 p-2 text-error" />
                </div>
                <h3 class="mb-1 text-headline-lg text-error">{{ number_format($unshelvedBooks) }}</h3>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-gutter md:grid-cols-2">
            <div class="flex flex-col rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-label-sm text-on-surface-variant">{{ __('عدد المؤلفين') }}</p>
                    <x-icon name="person" class="rounded-lg bg-primary/10 p-2 text-primary" />
                </div>
                <h3 class="text-headline-lg text-primary">{{ number_format($totalAuthors) }}</h3>
            </div>
            <div class="flex flex-col rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-label-sm text-on-surface-variant">{{ __('عدد الأقسام العلمية') }}</p>
                    <x-icon name="category" class="rounded-lg bg-primary/10 p-2 text-primary" />
                </div>
                <h3 class="text-headline-lg text-primary">{{ number_format($totalCategories) }}</h3>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-gutter lg:grid-cols-2">
            <!-- Category distribution -->
            <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <h4 class="mb-6 text-headline-md text-on-surface">{{ __('توزيع الكتب حسب القسم') }}</h4>

                @if ($categoryBreakdown->isNotEmpty())
                    <div class="flex flex-col gap-3">
                        @php $max = max(1, $categoryBreakdown->max('books_count')); @endphp
                        @foreach ($categoryBreakdown as $category)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-label-sm">
                                    <span class="font-bold text-on-surface">{{ $category->name }}</span>
                                    <span class="text-on-surface-variant">{{ $category->books_count }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-surface-container-high">
                                    <div class="h-2 rounded-full bg-primary" style="width: {{ max(4, ($category->books_count / $max) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-body-md text-on-surface-variant">{{ __('لا توجد بيانات بعد.') }}</p>
                @endif
            </div>

            <!-- Recent movements -->
            <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <h4 class="mb-6 text-headline-md text-on-surface">{{ __('آخر عمليات نقل الكتب') }}</h4>

                @if ($recentMovements->isNotEmpty())
                    <ul class="flex flex-col gap-4">
                        @foreach ($recentMovements as $movement)
                            <li class="flex items-start gap-3 {{ ! $loop->first ? 'border-t border-outline-variant pt-4' : '' }}">
                                <x-icon name="move_item" class="mt-0.5 text-secondary" />
                                <div>
                                    <p class="font-bold text-on-surface">{{ $movement->book->title }}</p>
                                    <p class="text-caption text-on-surface-variant">
                                        {{ $movement->toShelf ? __('نُقل إلى :location', ['location' => $movement->toShelf->codePrefix().'-'.$movement->to_position]) : __('أُزيل من الرف') }}
                                        @if ($movement->movedBy)
                                            — {{ $movement->movedBy->name }}
                                        @endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-body-md text-on-surface-variant">{{ __('لا توجد عمليات نقل بعد.') }}</p>
                @endif
            </div>
        </section>

        <!-- Recently added books -->
        <section class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-outline-variant p-6">
                <h4 class="text-headline-md text-on-surface">{{ __('أحدث الكتب المضافة') }}</h4>
                <a href="{{ route('admin.books.index') }}" wire:navigate class="font-bold text-primary hover:underline">{{ __('إدارة الكتب') }}</a>
            </div>

            @if ($recentBooks->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container-low text-label-bold text-on-surface-variant">
                                <th class="px-6 py-4">{{ __('العنوان') }}</th>
                                <th class="px-6 py-4">{{ __('المؤلف') }}</th>
                                <th class="px-6 py-4">{{ __('القسم') }}</th>
                                <th class="px-6 py-4 text-center">{{ __('الحالة') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @foreach ($recentBooks as $book)
                                <tr class="transition-colors hover:bg-surface-container-low">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('books.show', $book) }}" wire:navigate class="text-label-bold font-bold text-primary hover:underline">{{ $book->title }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-body-md">{{ $book->author?->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-body-md">{{ $book->category?->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <x-badge :variant="$book->status->badgeVariant()">{{ $book->status->label() }}</x-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="p-6 text-body-md text-on-surface-variant">{{ __('لا توجد كتب بعد. ابدأ بإضافة أول كتاب.') }}</p>
            @endif
        </section>
    </div>
</x-layouts::app>
