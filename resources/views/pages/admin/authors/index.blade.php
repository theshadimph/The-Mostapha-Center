<?php

use App\Models\Author;
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

    public string $bio = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('create', Author::class);

        $this->reset(['editingId', 'name', 'bio']);
        $this->showModal = true;
    }

    public function edit(Author $author): void
    {
        $this->authorize('update', $author);

        $this->editingId = $author->id;
        $this->name = $author->name;
        $this->bio = (string) $author->bio;
        $this->showModal = true;
    }

    public function save(): void
    {
        $author = $this->editingId ? Author::findOrFail($this->editingId) : new Author;

        $this->authorize($this->editingId ? 'update' : 'create', $this->editingId ? $author : Author::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:4000'],
        ]);

        if (! $this->editingId) {
            $validated['slug'] = Author::uniqueSlug($validated['name']);
        }

        $author->fill($validated)->save();

        $this->showModal = false;
        $this->dispatch('toast-show', variant: 'success', text: __('تم حفظ المؤلف بنجاح.'));
    }

    public function delete(Author $author): void
    {
        $this->authorize('delete', $author);

        if ($author->books()->exists()) {
            $this->dispatch('toast-show', variant: 'error', text: __('لا يمكن حذف مؤلف مرتبط بكتب.'));

            return;
        }

        $author->delete();
        $this->dispatch('toast-show', variant: 'success', text: __('تم حذف المؤلف.'));
    }

    public function with(): array
    {
        return [
            'authors' => Author::query()
                ->withCount('books')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(15),
        ];
    }
}; ?>

<div class="flex flex-col gap-gutter">
    <div class="flex items-center justify-between">
        <x-heading size="lg">{{ __('المؤلفون') }}</x-heading>
        <x-button wire:click="create" variant="primary">
            <x-icon name="add" class="text-lg" />
            {{ __('إضافة مؤلف') }}
        </x-button>
    </div>

    <x-input wire:model.live.debounce.300ms="search" icon="search" :placeholder="__('ابحث عن مؤلف...')" />

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
                    @forelse ($authors as $author)
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-6 py-4 font-bold text-primary">{{ $author->name }}</td>
                            <td class="px-6 py-4 text-body-md">{{ $author->books_count }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" wire:click="edit({{ $author->id }})" class="rounded-full p-2 text-primary hover:bg-primary/10">
                                        <x-icon name="edit" />
                                    </button>
                                    <button type="button" wire:click="delete({{ $author->id }})" wire:confirm="{{ __('هل أنت متأكد من حذف هذا المؤلف؟') }}" class="rounded-full p-2 text-error hover:bg-error/10">
                                        <x-icon name="delete" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-body-md text-on-surface-variant">{{ __('لا يوجد مؤلفون بعد.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $authors->links() }}

    <x-modal name="author-form" wire-model="showModal">
        <form wire:submit="save" class="flex flex-col gap-4">
            <x-heading size="md">{{ $editingId ? __('تعديل المؤلف') : __('إضافة مؤلف') }}</x-heading>

            <x-input wire:model="name" :label="__('اسم المؤلف')" required />
            <div>
                <label class="mb-1.5 block text-label-bold font-bold text-primary">{{ __('نبذة') }}</label>
                <textarea wire:model="bio" rows="4" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-body-md outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
            </div>

            <div class="mt-2 flex justify-end gap-2">
                <x-button type="button" variant="ghost" wire:click="$set('showModal', false)">{{ __('إلغاء') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('حفظ') }}</x-button>
            </div>
        </form>
    </x-modal>
</div>
