<?php

use App\Models\LibrarySection;
use App\Models\Shelf;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
{
    public bool $showSectionModal = false;

    public ?int $editingSectionId = null;

    public string $name = '';

    public string $code = '';

    public string $floor = '';

    public string $description = '';

    public bool $showShelvesModal = false;

    public ?int $activeSectionId = null;

    public string $newShelfCode = '';

    public ?int $editingShelfId = null;

    public string $editingShelfCode = '';

    public function createSection(): void
    {
        $this->authorize('create', LibrarySection::class);

        $this->reset(['editingSectionId', 'name', 'code', 'floor', 'description']);
        $this->showSectionModal = true;
    }

    public function editSection(LibrarySection $section): void
    {
        $this->authorize('update', $section);

        $this->editingSectionId = $section->id;
        $this->name = $section->name;
        $this->code = $section->code;
        $this->floor = (string) $section->floor;
        $this->description = (string) $section->description;
        $this->showSectionModal = true;
    }

    public function saveSection(): void
    {
        $section = $this->editingSectionId ? LibrarySection::findOrFail($this->editingSectionId) : new LibrarySection;

        $this->authorize($this->editingSectionId ? 'update' : 'create', $this->editingSectionId ? $section : LibrarySection::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:16', 'alpha_dash', 'unique:library_sections,code,'.$this->editingSectionId],
            'floor' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $section->fill($validated)->save();

        $this->showSectionModal = false;
        $this->dispatch('toast-show', variant: 'success', text: __('تم حفظ القاعة بنجاح.'));
    }

    public function deleteSection(LibrarySection $section): void
    {
        $this->authorize('delete', $section);

        if ($section->shelves()->whereHas('books')->exists()) {
            $this->dispatch('toast-show', variant: 'error', text: __('لا يمكن حذف قاعة تحتوي على أرفف بها كتب.'));

            return;
        }

        $section->delete();
        $this->dispatch('toast-show', variant: 'success', text: __('تم حذف القاعة.'));
    }

    public function manageShelves(LibrarySection $section): void
    {
        $this->authorize('update', $section);

        $this->activeSectionId = $section->id;
        $this->newShelfCode = '';
        $this->showShelvesModal = true;
    }

    public function addShelf(): void
    {
        $section = LibrarySection::findOrFail($this->activeSectionId);
        $this->authorize('update', $section);

        $validated = $this->validate([
            'newShelfCode' => ['required', 'string', 'max:16', 'alpha_dash', 'unique:shelves,code,NULL,id,library_section_id,'.$section->id],
        ], attributes: ['newShelfCode' => __('رمز الرف')]);

        $section->shelves()->create(['code' => $validated['newShelfCode']]);

        $this->newShelfCode = '';
        $this->dispatch('toast-show', variant: 'success', text: __('تمت إضافة الرف.'));
    }

    public function deleteShelf(Shelf $shelf): void
    {
        $this->authorize('update', $shelf->librarySection);

        if ($shelf->books()->exists()) {
            $this->dispatch('toast-show', variant: 'error', text: __('لا يمكن حذف رف يحتوي على كتب.'));

            return;
        }

        $shelf->delete();
        $this->dispatch('toast-show', variant: 'success', text: __('تم حذف الرف.'));
    }

    public function with(): array
    {
        return [
            'sections' => LibrarySection::query()
                ->withCount(['shelves', 'books'])
                ->orderBy('code')
                ->get(),
            'activeSection' => $this->activeSectionId
                ? LibrarySection::with(['shelves' => fn ($q) => $q->withCount('books')->orderBy('code')])->find($this->activeSectionId)
                : null,
        ];
    }
}; ?>

<div class="flex flex-col gap-gutter">
    <div class="flex items-center justify-between">
        <x-heading size="lg">{{ __('القاعات والأرفف') }}</x-heading>
        <x-button wire:click="createSection" variant="primary">
            <x-icon name="add" class="text-lg" />
            {{ __('إضافة قاعة') }}
        </x-button>
    </div>

    <div class="grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-3">
        @forelse ($sections as $section)
            <div class="flex flex-col gap-4 rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-caption font-bold uppercase text-on-surface-variant">{{ $section->code }}</p>
                        <h3 class="text-headline-md text-primary">{{ $section->name }}</h3>
                        @if ($section->floor)
                            <p class="text-caption text-on-surface-variant">{{ __('الطابق :floor', ['floor' => $section->floor]) }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="editSection({{ $section->id }})" class="rounded-full p-2 text-primary hover:bg-primary/10">
                            <x-icon name="edit" />
                        </button>
                        <button type="button" wire:click="deleteSection({{ $section->id }})" wire:confirm="{{ __('هل أنت متأكد من حذف هذه القاعة؟') }}" class="rounded-full p-2 text-error hover:bg-error/10">
                            <x-icon name="delete" />
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-body-md text-on-surface-variant">
                    <span class="flex items-center gap-1"><x-icon name="shelves" class="text-lg" /> {{ __(':count رف', ['count' => $section->shelves_count]) }}</span>
                    <span class="flex items-center gap-1"><x-icon name="menu_book" class="text-lg" /> {{ __(':count كتاب', ['count' => $section->books_count]) }}</span>
                </div>

                <x-button wire:click="manageShelves({{ $section->id }})" variant="outline" size="sm">
                    {{ __('إدارة الأرفف') }}
                </x-button>
            </div>
        @empty
            <p class="text-body-md text-on-surface-variant">{{ __('لا توجد قاعات بعد.') }}</p>
        @endforelse
    </div>

    <x-modal name="section-form" wire-model="showSectionModal">
        <form wire:submit="saveSection" class="flex flex-col gap-4">
            <x-heading size="md">{{ $editingSectionId ? __('تعديل القاعة') : __('إضافة قاعة') }}</x-heading>

            <x-input wire:model="name" :label="__('اسم القاعة')" required />
            <x-input wire:model="code" :label="__('الرمز (مثال: F01)')" required dir="ltr" />
            <x-input wire:model="floor" :label="__('الطابق')" />
            <div>
                <label class="mb-1.5 block text-label-bold font-bold text-primary">{{ __('الوصف') }}</label>
                <textarea wire:model="description" rows="2" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-body-md outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
            </div>

            <div class="mt-2 flex justify-end gap-2">
                <x-button type="button" variant="ghost" wire:click="$set('showSectionModal', false)">{{ __('إلغاء') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('حفظ') }}</x-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="shelves-manager" wire-model="showShelvesModal">
        @if ($activeSection)
            <div class="flex flex-col gap-4">
                <x-heading size="md">{{ __('أرفف :section', ['section' => $activeSection->name]) }}</x-heading>

                <form wire:submit="addShelf" class="flex items-end gap-2">
                    <div class="flex-1">
                        <x-input wire:model="newShelfCode" :label="__('رمز الرف الجديد')" dir="ltr" />
                    </div>
                    <x-button type="submit" variant="secondary">{{ __('إضافة') }}</x-button>
                </form>

                <div class="max-h-80 overflow-y-auto rounded-lg border border-outline-variant">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container-low text-label-bold text-on-surface-variant">
                                <th class="px-4 py-2">{{ __('الرمز') }}</th>
                                <th class="px-4 py-2">{{ __('الكتب') }}</th>
                                <th class="px-4 py-2 text-center">{{ __('إجراءات') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @forelse ($activeSection->shelves as $shelf)
                                <tr>
                                    <td class="px-4 py-2 font-bold text-primary" dir="ltr">{{ $shelf->code }}</td>
                                    <td class="px-4 py-2">{{ $shelf->books_count }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" wire:click="deleteShelf({{ $shelf->id }})" wire:confirm="{{ __('هل أنت متأكد من حذف هذا الرف؟') }}" class="rounded-full p-2 text-error hover:bg-error/10">
                                            <x-icon name="delete" class="text-lg" />
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-on-surface-variant">{{ __('لا توجد أرفف في هذه القاعة بعد.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end">
                    <x-button type="button" variant="ghost" wire:click="$set('showShelvesModal', false)">{{ __('إغلاق') }}</x-button>
                </div>
            </div>
        @endif
    </x-modal>
</div>
