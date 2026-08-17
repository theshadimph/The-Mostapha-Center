<?php

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public ?int $parent_id = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('create', Category::class);

        $this->reset(['editingId', 'name', 'description', 'parent_id']);
        $this->showModal = true;
    }

    public function edit(Category $category): void
    {
        $this->authorize('update', $category);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = (string) $category->description;
        $this->parent_id = $category->parent_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $category = $this->editingId ? Category::findOrFail($this->editingId) : new Category;

        $this->authorize($this->editingId ? 'update' : 'create', $this->editingId ? $category : Category::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                $this->editingId ? Rule::notIn([$this->editingId]) : 'nullable',
            ],
        ], attributes: ['parent_id' => __('القسم الرئيسي')]);

        if (! $this->editingId) {
            $validated['slug'] = Category::uniqueSlug($validated['name']);
        }

        $category->fill($validated)->save();

        $this->showModal = false;
        $this->dispatch('toast-show', variant: 'success', text: __('تم حفظ القسم بنجاح.'));
    }

    public function delete(Category $category): void
    {
        $this->authorize('delete', $category);

        if ($category->books()->exists()) {
            $this->dispatch('toast-show', variant: 'error', text: __('لا يمكن حذف قسم يحتوي على كتب.'));

            return;
        }

        if ($category->children()->exists()) {
            $this->dispatch('toast-show', variant: 'error', text: __('لا يمكن حذف قسم يحتوي على أقسام فرعية.'));

            return;
        }

        $category->delete();
        $this->dispatch('toast-show', variant: 'success', text: __('تم حذف القسم.'));
    }

    public function with(): array
    {
        return [
            'categories' => Category::query()
                ->withCount('books')
                ->with('parent')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(15),
            'parentOptions' => Category::query()
                ->whereNull('parent_id')
                ->when($this->editingId, fn ($q) => $q->whereKeyNot($this->editingId))
                ->orderBy('name')
                ->get(),
        ];
    }
}; ?>

<div class="flex flex-col gap-gutter">
    <div class="flex items-center justify-between">
        <x-heading size="lg">{{ __('الأقسام العلمية') }}</x-heading>
        <x-button wire:click="create" variant="primary">
            <x-icon name="add" class="text-lg" />
            {{ __('إضافة قسم') }}
        </x-button>
    </div>

    <x-input wire:model.live.debounce.300ms="search" icon="search" :placeholder="__('ابحث عن قسم...')" />

    <div class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="border-b border-outline-variant bg-surface-container-low text-label-bold text-on-surface-variant">
                        <th class="px-6 py-4">{{ __('الاسم') }}</th>
                        <th class="px-6 py-4">{{ __('عدد الكتب') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('إجراءات') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-6 py-4">
                                <p class="font-bold text-primary">
                                    @if ($category->parent)
                                        <span class="text-on-surface-variant">{{ $category->parent->name }} /</span>
                                    @endif
                                    {{ $category->name }}
                                </p>
                                @if ($category->description)
                                    <p class="text-caption text-on-surface-variant">{{ $category->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-body-md">{{ $category->books_count }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" wire:click="edit({{ $category->id }})" class="rounded-full p-2 text-primary hover:bg-primary/10">
                                        <x-icon name="edit" />
                                    </button>
                                    <button type="button" wire:click="delete({{ $category->id }})" wire:confirm="{{ __('هل أنت متأكد من حذف هذا القسم؟') }}" class="rounded-full p-2 text-error hover:bg-error/10">
                                        <x-icon name="delete" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-body-md text-on-surface-variant">{{ __('لا توجد أقسام بعد.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $categories->links() }}

    <x-modal name="category-form" wire-model="showModal">
        <form wire:submit="save" class="flex flex-col gap-4">
            <x-heading size="md">{{ $editingId ? __('تعديل القسم') : __('إضافة قسم') }}</x-heading>

            <x-input wire:model="name" :label="__('اسم القسم')" required />

            <x-select wire:model="parent_id" :label="__('القسم الرئيسي (اختياري)')">
                <option value="">{{ __('— قسم رئيسي (بدون أب) —') }}</option>
                @foreach ($parentOptions as $parentOption)
                    <option value="{{ $parentOption->id }}">{{ $parentOption->name }}</option>
                @endforeach
            </x-select>

            <div>
                <label class="mb-1.5 block text-label-bold font-bold text-primary">{{ __('الوصف') }}</label>
                <textarea wire:model="description" rows="3" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-body-md outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
            </div>

            <div class="mt-2 flex justify-end gap-2">
                <x-button type="button" variant="ghost" wire:click="$set('showModal', false)">{{ __('إلغاء') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('حفظ') }}</x-button>
            </div>
        </form>
    </x-modal>
</div>
