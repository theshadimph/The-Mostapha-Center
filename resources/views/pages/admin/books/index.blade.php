<?php

use App\Actions\Library\MoveBookToLocation;
use App\Enums\BookStatus;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Shelf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts::app')] class extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $categoryFilter = '';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    public string $title = '';

    public ?int $author_id = null;

    /** @var list<string> */
    public array $editors = [''];

    /** @var list<string> */
    public array $commentators = [''];

    /** @var list<string> */
    public array $annotators = [''];

    public ?int $category_id = null;

    public string $description = '';

    public string $publisher = '';

    public ?int $publication_year = null;

    public string $edition = '';

    public string $isbn = '';

    public ?int $pages = null;

    public string $language = 'ar';

    public string $status = 'available';

    public $cover = null;

    public bool $showMoveModal = false;

    public ?int $movingBookId = null;

    public ?int $moveShelfId = null;

    public string $movePosition = '';

    public bool $showNewAuthor = false;

    public string $newAuthorName = '';

    public bool $showNewCategory = false;

    public string $newCategoryName = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('create', Book::class);

        $this->reset([
            'editingId', 'title', 'author_id', 'category_id', 'description', 'publisher',
            'publication_year', 'edition', 'isbn', 'pages', 'language', 'status', 'cover',
            'showNewAuthor', 'newAuthorName', 'showNewCategory', 'newCategoryName',
            'editors', 'commentators', 'annotators',
        ]);
        $this->language = 'ar';
        $this->status = BookStatus::Available->value;
        $this->showFormModal = true;
    }

    // Book's route key is `slug` (for public URLs), but the admin table calls
    // these actions with the numeric id — resolving explicitly by primary key
    // here avoids Livewire's implicit binding trying `where('slug', $id)`.
    public function edit(int $bookId): void
    {
        $book = Book::findOrFail($bookId);
        $this->authorize('update', $book);

        $this->editingId = $book->id;
        $this->title = $book->title;
        $this->author_id = $book->author_id;
        $this->editors = $book->editors ?: [''];
        $this->commentators = $book->commentators ?: [''];
        $this->annotators = $book->annotators ?: [''];
        $this->category_id = $book->category_id;
        $this->description = (string) $book->description;
        $this->publisher = (string) $book->publisher;
        $this->publication_year = $book->publication_year;
        $this->edition = (string) $book->edition;
        $this->isbn = (string) $book->isbn;
        $this->pages = $book->pages;
        $this->language = $book->language;
        $this->status = $book->status->value;
        $this->cover = null;
        $this->showNewAuthor = false;
        $this->newAuthorName = '';
        $this->showNewCategory = false;
        $this->newCategoryName = '';
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $book = $this->editingId ? Book::findOrFail($this->editingId) : new Book;

        $this->authorize($this->editingId ? 'update' : 'create', $this->editingId ? $book : Book::class);

        $this->editors = $this->cleanContributorList($this->editors);
        $this->commentators = $this->cleanContributorList($this->commentators);
        $this->annotators = $this->cleanContributorList($this->annotators);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'author_id' => ['nullable', 'exists:authors,id'],
            'editors' => ['array', 'max:20'],
            'editors.*' => ['string', 'max:255'],
            'commentators' => ['array', 'max:20'],
            'commentators.*' => ['string', 'max:255'],
            'annotators' => ['array', 'max:20'],
            'annotators.*' => ['string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:'.(date('Y') + 1)],
            'edition' => ['nullable', 'string', 'max:100'],
            'isbn' => ['nullable', 'string', 'max:32', Rule::unique('books', 'isbn')->ignore($this->editingId)],
            'pages' => ['nullable', 'integer', 'min:1', 'max:20000'],
            'language' => ['required', 'string', 'max:8'],
            'status' => ['required', Rule::enum(BookStatus::class)],
            'cover' => ['nullable', 'image', 'max:4096'],
        ], attributes: [
            'title' => __('العنوان'),
            'author_id' => __('المؤلف'),
            'editors' => __('المحققون'),
            'commentators' => __('الشارحون'),
            'annotators' => __('المحشّون'),
            'category_id' => __('القسم'),
            'isbn' => __('رقم الردمك'),
        ]);

        unset($validated['cover']);

        $validated['editors'] = $validated['editors'] ?: null;
        $validated['commentators'] = $validated['commentators'] ?: null;
        $validated['annotators'] = $validated['annotators'] ?: null;

        if (! $this->editingId) {
            $validated['slug'] = Book::uniqueSlug($this->title);
        }

        $book->fill($validated);

        if ($this->cover) {
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }

            $book->cover_image = $this->cover->store('book-covers', 'public');
        }

        $book->save();

        $this->showFormModal = false;
        $this->dispatch('toast-show', variant: 'success', text: __('تم حفظ الكتاب بنجاح.'));
    }

    private const array CONTRIBUTOR_FIELDS = ['editors', 'commentators', 'annotators'];

    public function addContributor(string $field): void
    {
        if (! in_array($field, self::CONTRIBUTOR_FIELDS, true)) {
            return;
        }

        $this->{$field}[] = '';
    }

    public function removeContributor(string $field, int $index): void
    {
        if (! in_array($field, self::CONTRIBUTOR_FIELDS, true)) {
            return;
        }

        unset($this->{$field}[$index]);
        $this->{$field} = array_values($this->{$field});
    }

    /**
     * @param  list<string>  $names
     * @return list<string>
     */
    private function cleanContributorList(array $names): array
    {
        return collect($names)
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn ($name) => $name !== '')
            ->values()
            ->all();
    }

    public function addAuthor(): void
    {
        $this->authorize('create', Author::class);

        $name = trim($this->newAuthorName);

        if ($name === '') {
            return;
        }

        $author = Author::create(['name' => $name]);

        $this->author_id = $author->id;
        $this->newAuthorName = '';
        $this->showNewAuthor = false;
    }

    public function addCategory(): void
    {
        $this->authorize('create', Category::class);

        $name = trim($this->newCategoryName);

        if ($name === '') {
            return;
        }

        $category = Category::create(['name' => $name]);

        $this->category_id = $category->id;
        $this->newCategoryName = '';
        $this->showNewCategory = false;
    }

    public function delete(int $bookId): void
    {
        $book = Book::findOrFail($bookId);
        $this->authorize('delete', $book);

        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();
        $this->dispatch('toast-show', variant: 'success', text: __('تم حذف الكتاب.'));
    }

    public function openMoveModal(int $bookId): void
    {
        $book = Book::findOrFail($bookId);
        $this->authorize('move', $book);

        $this->movingBookId = $book->id;
        $this->moveShelfId = $book->shelf_id;
        $this->movePosition = (string) ($book->position ?? '');
        $this->showMoveModal = true;
    }

    public function move(MoveBookToLocation $action): void
    {
        $book = Book::findOrFail($this->movingBookId);
        $this->authorize('move', $book);

        $validated = $this->validate([
            'moveShelfId' => ['nullable', 'exists:shelves,id'],
            'movePosition' => ['nullable', 'string', 'max:16', 'required_with:moveShelfId'],
        ], attributes: ['moveShelfId' => __('الرف'), 'movePosition' => __('الموقع')]);

        $shelf = $validated['moveShelfId'] ? Shelf::find($validated['moveShelfId']) : null;

        $action->handle($book, $shelf, $shelf ? $validated['movePosition'] : null, auth()->user());

        $this->showMoveModal = false;
        $this->dispatch('toast-show', variant: 'success', text: __('تم تحديث موقع الكتاب.'));
    }

    public function with(): array
    {
        return [
            'books' => Book::query()
                ->when($this->search, function ($q) {
                    // Simple substring filter over the admin table — not the
                    // public Scout/Meilisearch-powered search, just a quick
                    // "find the row I'm editing" tool for staff.
                    $like = '%'.$this->search.'%';

                    $q->where(function ($q) use ($like) {
                        $q->where('title', 'like', $like)
                            ->orWhere('isbn', 'like', $like)
                            ->orWhereHas('author', fn ($q) => $q->where('name', 'like', $like));
                    });
                })
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
                ->with(['author', 'category', 'shelf.librarySection'])
                ->latest()
                ->paginate(15),
            'authors' => Author::query()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
            'shelves' => Shelf::query()->with('librarySection')->get(),
            'statuses' => BookStatus::all(),
        ];
    }
}; ?>

<div class="flex flex-col gap-gutter">
    <div class="flex items-center justify-between">
        <x-heading size="lg">{{ __('الكتب') }}</x-heading>
        <x-button wire:click="create" variant="primary">
            <x-icon name="add" class="text-lg" />
            {{ __('إضافة كتاب') }}
        </x-button>
    </div>

    <div class="flex flex-wrap gap-3">
        <div class="min-w-[240px] flex-1">
            <x-input wire:model.live.debounce.300ms="search" icon="search" :placeholder="__('ابحث بالعنوان، المؤلف، أو الردمك...')" />
        </div>
        <select wire:model.live="statusFilter" class="h-12 rounded-lg border border-outline-variant bg-surface-container-lowest px-4 text-body-md outline-none focus:border-primary">
            <option value="">{{ __('كل الحالات') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
        <select wire:model.live="categoryFilter" class="h-12 rounded-lg border border-outline-variant bg-surface-container-lowest px-4 text-body-md outline-none focus:border-primary">
            <option value="">{{ __('كل الأقسام') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="border-b border-outline-variant bg-surface-container-low text-label-bold text-on-surface-variant">
                        <th class="px-6 py-4">{{ __('الغلاف') }}</th>
                        <th class="px-6 py-4">{{ __('العنوان') }}</th>
                        <th class="px-6 py-4">{{ __('المؤلف') }}</th>
                        <th class="px-6 py-4">{{ __('القسم') }}</th>
                        <th class="px-6 py-4">{{ __('الموقع') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('الحالة') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('إجراءات') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($books as $book)
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-6 py-4">
                                <div class="flex h-16 w-12 items-center justify-center overflow-hidden rounded border border-outline-variant bg-surface-container-high">
                                    @if ($book->cover_url)
                                        <img src="{{ $book->cover_url }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <x-icon name="auto_stories" class="text-xl text-outline" />
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('books.show', $book) }}" wire:navigate target="_blank" class="text-label-bold font-bold text-primary hover:underline">{{ $book->title }}</a>
                                @if ($book->isbn)
                                    <p class="text-caption text-on-surface-variant" dir="ltr">ISBN: {{ $book->isbn }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-body-md">{{ $book->author?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-body-md">{{ $book->category?->name ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <button type="button" wire:click="openMoveModal({{ $book->id }})" class="rounded bg-surface-container-high px-2 py-1 text-caption font-bold hover:bg-secondary-container" dir="ltr">
                                    {{ $book->location_code ?? __('غير محدد') }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <x-badge :variant="$book->status->badgeVariant()">{{ $book->status->label() }}</x-badge>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" wire:click="edit({{ $book->id }})" class="rounded-full p-2 text-primary hover:bg-primary/10">
                                        <x-icon name="edit" />
                                    </button>
                                    <button type="button" wire:click="delete({{ $book->id }})" wire:confirm="{{ __('هل أنت متأكد من حذف هذا الكتاب؟') }}" class="rounded-full p-2 text-error hover:bg-error/10">
                                        <x-icon name="delete" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-body-md text-on-surface-variant">{{ __('لا توجد كتب مطابقة.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $books->links() }}

    <!-- Create/Edit Modal -->
    <x-modal name="book-form" wire-model="showFormModal">
        <form wire:submit="save" class="flex max-h-[75vh] flex-col gap-4 overflow-y-auto p-1">
            <x-heading size="md">{{ $editingId ? __('تعديل الكتاب') : __('إضافة كتاب') }}</x-heading>

            <x-input wire:model="title" :label="__('العنوان')" required />

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="flex items-center justify-between">
                        <label class="mb-1.5 block text-label-bold font-bold text-primary">{{ __('المؤلف') }}</label>
                        <button type="button" wire:click="$toggle('showNewAuthor')" class="text-caption font-bold text-primary hover:underline">
                            {{ $showNewAuthor ? __('إلغاء') : __('+ مؤلف جديد') }}
                        </button>
                    </div>

                    @if ($showNewAuthor)
                        <div class="flex gap-2">
                            <input type="text" wire:model="newAuthorName" wire:keydown.enter.prevent="addAuthor" placeholder="{{ __('اسم المؤلف') }}" class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 text-body-md outline-none focus:border-primary">
                            <x-button type="button" wire:click="addAuthor" variant="primary">{{ __('إضافة') }}</x-button>
                        </div>
                    @else
                        <select wire:model="author_id" class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 text-body-md outline-none focus:border-primary">
                            <option value="">{{ __('— بدون —') }}</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->id }}">{{ $author->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label class="mb-1.5 block text-label-bold font-bold text-primary">{{ __('القسم العلمي') }}</label>
                        <button type="button" wire:click="$toggle('showNewCategory')" class="text-caption font-bold text-primary hover:underline">
                            {{ $showNewCategory ? __('إلغاء') : __('+ قسم جديد') }}
                        </button>
                    </div>

                    @if ($showNewCategory)
                        <div class="flex gap-2">
                            <input type="text" wire:model="newCategoryName" wire:keydown.enter.prevent="addCategory" placeholder="{{ __('اسم القسم') }}" class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 text-body-md outline-none focus:border-primary">
                            <x-button type="button" wire:click="addCategory" variant="primary">{{ __('إضافة') }}</x-button>
                        </div>
                    @else
                        <select wire:model="category_id" class="h-12 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 text-body-md outline-none focus:border-primary">
                            <option value="">{{ __('— بدون —') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <x-contributor-list field="editors" :label="__('المحققون')" :values="$editors" />
                <x-contributor-list field="commentators" :label="__('الشارحون')" :values="$commentators" />
                <x-contributor-list field="annotators" :label="__('المحشّون')" :values="$annotators" />
            </div>

            <div>
                <label class="mb-1.5 block text-label-bold font-bold text-primary">{{ __('الوصف') }}</label>
                <textarea wire:model="description" rows="3" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-body-md outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-input wire:model="publisher" :label="__('الناشر')" />
                <x-input wire:model="publication_year" type="number" :label="__('سنة النشر')" />
                <x-input wire:model="edition" :label="__('الطبعة')" />
                <x-input wire:model="isbn" :label="__('رقم الردمك (ISBN)')" dir="ltr" />
                <x-input wire:model="pages" type="number" :label="__('عدد الصفحات')" />
                <x-select wire:model="language" :label="__('اللغة')">
                    <option value="ar">{{ __('العربية') }}</option>
                    <option value="en">{{ __('الإنجليزية') }}</option>
                    <option value="other">{{ __('أخرى') }}</option>
                </x-select>
            </div>

            <x-select wire:model="status" :label="__('الحالة')">
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                @endforeach
            </x-select>

            <div>
                <label class="mb-1.5 block text-label-bold font-bold text-primary">{{ __('صورة الغلاف') }}</label>
                <input type="file" wire:model="cover" accept="image/*" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-body-md outline-none focus:border-primary">
                @if ($cover && $cover->isPreviewable())
                    <img src="{{ $cover->temporaryUrl() }}" class="mt-2 h-24 w-16 rounded object-cover" alt="">
                @endif
            </div>

            <div class="mt-2 flex justify-end gap-2">
                <x-button type="button" variant="ghost" wire:click="$set('showFormModal', false)">{{ __('إلغاء') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('حفظ') }}</x-button>
            </div>
        </form>
    </x-modal>

    <!-- Move Location Modal -->
    <x-modal name="move-book" wire-model="showMoveModal">
        <form wire:submit="move" class="flex flex-col gap-4">
            <x-heading size="md">{{ __('تحديد موقع الكتاب') }}</x-heading>

            <x-select wire:model="moveShelfId" :label="__('الرف')">
                <option value="">{{ __('— بدون رف (غير موزّع) —') }}</option>
                @foreach ($shelves as $shelf)
                    <option value="{{ $shelf->id }}">{{ $shelf->librarySection->name }} — {{ $shelf->code }}</option>
                @endforeach
            </x-select>

            <x-input wire:model="movePosition" :label="__('الموقع على الرف')" dir="ltr" />

            <div class="mt-2 flex justify-end gap-2">
                <x-button type="button" variant="ghost" wire:click="$set('showMoveModal', false)">{{ __('إلغاء') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('حفظ الموقع') }}</x-button>
            </div>
        </form>
    </x-modal>
</div>
