<?php

use App\Models\ImportBatch;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] class extends Component
{
    use WithPagination;

    public function delete(ImportBatch $importBatch): void
    {
        Gate::authorize('manage-catalog');

        Storage::disk('local')->delete($importBatch->stored_path);
        $importBatch->delete();

        $this->dispatch('toast-show', variant: 'success', text: __('تم حذف عملية الاستيراد.'));
    }

    public function with(): array
    {
        return [
            'batches' => ImportBatch::query()
                ->with('user')
                ->latest()
                ->paginate(15),
        ];
    }
}; ?>

<div class="flex flex-col gap-gutter">
    <div class="flex items-center justify-between">
        <x-heading size="lg">{{ __('استيراد الكتب من إكسل') }}</x-heading>
    </div>

    <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
        <x-heading size="md">{{ __('استيراد جديد') }}</x-heading>
        <p class="mt-1 text-body-md text-on-surface-variant">{{ __('ارفع ملف Excel أو CSV يحتوي على بيانات الكتب. ستتمكن من مطابقة الأعمدة والتصنيفات قبل تأكيد الاستيراد.') }}</p>

        {{-- A plain form POST, not a Livewire-bound file input — see ImportUploadController. --}}
        <form method="POST" action="{{ route('admin.import.store') }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-3 text-body-md outline-none focus:border-primary">
            @error('file')
                <p class="text-caption text-error">{{ $message }}</p>
            @enderror

            <div>
                <x-button type="submit" variant="primary">
                    <x-icon name="upload_file" class="text-lg" />
                    {{ __('رفع ومتابعة') }}
                </x-button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="border-b border-outline-variant bg-surface-container-low text-label-bold text-on-surface-variant">
                        <th class="px-6 py-4">{{ __('الملف') }}</th>
                        <th class="px-6 py-4">{{ __('الحالة') }}</th>
                        <th class="px-6 py-4">{{ __('عدد الصفوف') }}</th>
                        <th class="px-6 py-4">{{ __('تم استيرادها') }}</th>
                        <th class="px-6 py-4">{{ __('بواسطة') }}</th>
                        <th class="px-6 py-4">{{ __('التاريخ') }}</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($batches as $batch)
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.import.show', $batch) }}" wire:navigate class="font-bold text-primary hover:underline">{{ $batch->original_filename }}</a>
                            </td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$batch->status->badgeVariant()">{{ $batch->status->label() }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-body-md">{{ $batch->total_rows !== null ? number_format($batch->total_rows) : '—' }}</td>
                            <td class="px-6 py-4 text-body-md">{{ number_format($batch->imported_count) }}</td>
                            <td class="px-6 py-4 text-body-md">{{ $batch->user?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-caption text-on-surface-variant">{{ $batch->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-4">
                                <button type="button" wire:click="delete({{ $batch->id }})" wire:confirm="{{ __('هل أنت متأكد من حذف عملية الاستيراد هذه؟') }}" class="rounded-full p-2 text-error hover:bg-error/10">
                                    <x-icon name="delete" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-body-md text-on-surface-variant">{{ __('لا توجد عمليات استيراد بعد.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $batches->links() }}
</div>
