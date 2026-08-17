<?php

use App\Enums\ImportBatchStatus;
use App\Enums\ImportTargetField;
use App\Jobs\ProcessBookImport;
use App\Models\Category;
use App\Models\ImportBatch;
use App\Support\Import\ColumnGuesser;
use App\Support\Import\TextNormalizer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

new #[Layout('layouts::app')] class extends Component
{
    public ImportBatch $importBatch;

    /** @var list<string> */
    public array $headers = [];

    /** @var list<list<mixed>> */
    public array $previewRows = [];

    /** @var array<int, string> column index => ImportTargetField value ('' = ignored) */
    public array $columnMapping = [];

    /** @var list<string> */
    public array $distinctCategories = [];

    /** @var array<string, array{mode: string, category_id: ?int, name: string, parent_id: ?int}> */
    public array $categoryDecisions = [];

    public function mount(ImportBatch $importBatch): void
    {
        Gate::authorize('manage-catalog');

        $this->importBatch = $importBatch;

        if ($this->importBatch->status === ImportBatchStatus::Uploaded) {
            $this->loadHeadersAndPreview();
            $this->columnMapping = ColumnGuesser::guessAll($this->headers);
        } elseif ($this->importBatch->status === ImportBatchStatus::Mapping) {
            $this->loadHeadersAndPreview();
            $categoryColumnIndex = array_search(ImportTargetField::Category->value, $this->importBatch->column_mapping ?? [], true);

            if ($categoryColumnIndex !== false) {
                $this->collectDistinctCategories((int) $categoryColumnIndex);
            }
        }
    }

    private function readRows(): array
    {
        $path = Storage::disk('local')->path($this->importBatch->stored_path);

        return (Excel::toCollection(null, $path)->first() ?? collect())->toArray();
    }

    private function loadHeadersAndPreview(): void
    {
        $rows = $this->readRows();
        $this->headers = array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);
        $this->previewRows = array_slice($rows, 1, 8);
    }

    public function saveColumnMapping(): void
    {
        Gate::authorize('manage-catalog');

        $mapping = collect($this->columnMapping)->filter(fn ($v) => $v !== '' && $v !== null)->all();

        if (! in_array(ImportTargetField::Title->value, $mapping, true)) {
            $this->addError('columnMapping', __('يجب تحديد عمود «العنوان» على الأقل قبل المتابعة.'));

            return;
        }

        $this->importBatch->update(['column_mapping' => $mapping]);

        $categoryColumnIndex = array_search(ImportTargetField::Category->value, $mapping, true);

        if ($categoryColumnIndex !== false) {
            $this->collectDistinctCategories((int) $categoryColumnIndex);
            $this->importBatch->update(['status' => ImportBatchStatus::Mapping]);
        } else {
            $this->importBatch->update(['status' => ImportBatchStatus::Ready, 'category_mapping' => []]);
        }
    }

    private function collectDistinctCategories(int $columnIndex): void
    {
        $rows = $this->readRows();
        array_shift($rows);

        $values = [];
        foreach ($rows as $row) {
            $normalized = TextNormalizer::normalize((string) ($row[$columnIndex] ?? ''));

            if ($normalized !== '') {
                $values[$normalized] = true;
            }
        }

        $this->distinctCategories = array_keys($values);
        sort($this->distinctCategories, SORT_STRING | SORT_FLAG_CASE);

        $existingByName = Category::query()->get()->keyBy(fn (Category $c) => mb_strtolower($c->name));

        foreach ($this->distinctCategories as $raw) {
            $match = $existingByName->get(mb_strtolower($raw));

            $this->categoryDecisions[$raw] = [
                'mode' => $match ? 'map' : 'create',
                'category_id' => $match?->id,
                'name' => $raw,
                'parent_id' => null,
            ];
        }
    }

    public function saveCategoryMapping(): void
    {
        Gate::authorize('manage-catalog');

        $mapping = [];

        foreach ($this->categoryDecisions as $raw => $decision) {
            $mapping[$raw] = match ($decision['mode']) {
                'skip' => null,
                'map' => $decision['category_id'] ? (int) $decision['category_id'] : null,
                'create' => Category::create([
                    'name' => $name = (trim((string) $decision['name']) ?: $raw),
                    'slug' => Category::uniqueSlug($name),
                    'parent_id' => $decision['parent_id'] ?: null,
                ])->id,
                default => null,
            };
        }

        $this->importBatch->update(['category_mapping' => $mapping, 'status' => ImportBatchStatus::Ready]);
    }

    public function startImport(): void
    {
        Gate::authorize('manage-catalog');

        ProcessBookImport::dispatch($this->importBatch->id);
        $this->importBatch->refresh();
    }

    public function refreshProgress(): void
    {
        $this->importBatch->refresh();
    }

    public function with(): array
    {
        return [
            'targetFields' => ImportTargetField::all(),
            'existingCategories' => Category::query()->orderByRaw('parent_id is not null')->orderBy('name')->get(),
        ];
    }
}; ?>

<div class="flex flex-col gap-gutter">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.import.index') }}" wire:navigate class="text-caption font-bold text-primary hover:underline">{{ __('← كل عمليات الاستيراد') }}</a>
            <x-heading size="lg">{{ $importBatch->original_filename }}</x-heading>
        </div>
        <x-badge :variant="$importBatch->status->badgeVariant()">{{ $importBatch->status->label() }}</x-badge>
    </div>

    {{-- Step 1: column mapping --}}
    @if ($importBatch->status === App\Enums\ImportBatchStatus::Uploaded)
        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
            <x-heading size="md">{{ __('الخطوة 1: مطابقة الأعمدة') }}</x-heading>
            <p class="mt-1 text-body-md text-on-surface-variant">{{ __('حدد الحقل الذي يقابل كل عمود في الملف. الأعمدة غير المحددة سيتم تجاهلها.') }}</p>

            @error('columnMapping')
                <p class="mt-2 text-caption text-error">{{ $message }}</p>
            @enderror

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="border-b border-outline-variant text-label-bold text-on-surface-variant">
                            <th class="px-3 py-2">{{ __('عمود الملف') }}</th>
                            <th class="px-3 py-2">{{ __('عيّنة من البيانات') }}</th>
                            <th class="px-3 py-2">{{ __('يقابل الحقل') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach ($headers as $index => $header)
                            <tr>
                                <td class="px-3 py-2 font-bold text-primary">{{ $header !== '' ? $header : __('(عمود :n بدون عنوان)', ['n' => $index + 1]) }}</td>
                                <td class="px-3 py-2 text-caption text-on-surface-variant">
                                    {{ collect($previewRows)->pluck($index)->filter(fn ($v) => $v !== null && $v !== '')->take(3)->implode(' / ') ?: '—' }}
                                </td>
                                <td class="px-3 py-2">
                                    <select wire:model="columnMapping.{{ $index }}" class="h-10 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 text-body-md outline-none focus:border-primary">
                                        <option value="">{{ __('— تجاهل —') }}</option>
                                        @foreach ($targetFields as $field)
                                            <option value="{{ $field->value }}">{{ $field->label() }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <x-button wire:click="saveColumnMapping" variant="primary">{{ __('التالي') }}</x-button>
            </div>
        </div>
    @endif

    {{-- Step 2: category mapping --}}
    @if ($importBatch->status === App\Enums\ImportBatchStatus::Mapping)
        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
            <x-heading size="md">{{ __('الخطوة 2: مطابقة التصنيفات') }}</x-heading>
            <p class="mt-1 text-body-md text-on-surface-variant">{{ __('وُجد :n تصنيفاً مختلفاً في الملف. لكل تصنيف، اختر ربطه بتصنيف موجود أو إنشاء تصنيف جديد، أو تجاهله (سيُستورد الكتاب بدون تصنيف).', ['n' => count($distinctCategories)]) }}</p>

            <div class="mt-4 flex flex-col gap-4">
                @foreach ($distinctCategories as $raw)
                    <div class="grid grid-cols-1 gap-3 rounded-lg border border-outline-variant p-4 md:grid-cols-3">
                        <div class="font-bold text-on-surface">{{ $raw }}</div>

                        <select wire:model.live="categoryDecisions.{{ $raw }}.mode" class="h-10 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 text-body-md outline-none focus:border-primary">
                            <option value="map">{{ __('ربط بتصنيف موجود') }}</option>
                            <option value="create">{{ __('إنشاء تصنيف جديد') }}</option>
                            <option value="skip">{{ __('تجاهل (بدون تصنيف)') }}</option>
                        </select>

                        @if (($categoryDecisions[$raw]['mode'] ?? 'map') === 'map')
                            <select wire:model="categoryDecisions.{{ $raw }}.category_id" class="h-10 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 text-body-md outline-none focus:border-primary">
                                <option value="">{{ __('— اختر —') }}</option>
                                @foreach ($existingCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->parent ? $category->parent->name.' / ' : '' }}{{ $category->name }}</option>
                                @endforeach
                            </select>
                        @elseif (($categoryDecisions[$raw]['mode'] ?? '') === 'create')
                            <div class="flex flex-col gap-2">
                                <input type="text" wire:model="categoryDecisions.{{ $raw }}.name" class="h-10 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 text-body-md outline-none focus:border-primary" placeholder="{{ __('اسم التصنيف الجديد') }}">
                                <select wire:model="categoryDecisions.{{ $raw }}.parent_id" class="h-10 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 text-body-md outline-none focus:border-primary">
                                    <option value="">{{ __('— قسم رئيسي (بدون أب) —') }}</option>
                                    @foreach ($existingCategories->whereNull('parent_id') as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div></div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <x-button wire:click="saveCategoryMapping" variant="primary">{{ __('التالي') }}</x-button>
            </div>
        </div>
    @endif

    {{-- Step 3: confirm --}}
    @if ($importBatch->status === App\Enums\ImportBatchStatus::Ready)
        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
            <x-heading size="md">{{ __('الخطوة 3: تأكيد الاستيراد') }}</x-heading>

            <dl class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                <div>
                    <dt class="text-caption text-on-surface-variant">{{ __('الأعمدة المحددة') }}</dt>
                    <dd class="text-headline-md text-primary">{{ count($importBatch->column_mapping ?? []) }}</dd>
                </div>
                <div>
                    <dt class="text-caption text-on-surface-variant">{{ __('تصنيفات مربوطة') }}</dt>
                    <dd class="text-headline-md text-primary">{{ collect($importBatch->category_mapping ?? [])->filter()->count() }}</dd>
                </div>
            </dl>

            <p class="mt-4 text-body-md text-on-surface-variant">
                {{ __('سيتم فحص كل صف: الصفوف المكررة (بنفس الردمك أو الباركود أو العنوان والمؤلف والطبعة) والصفوف غير الصالحة لن تُستورد وستظهر في التقرير النهائي. يعمل الاستيراد في الخلفية ولا يلزم إبقاء هذه الصفحة مفتوحة.') }}
            </p>

            <div class="mt-4">
                <x-button wire:click="startImport" variant="primary">
                    <x-icon name="play_arrow" class="text-lg" />
                    {{ __('بدء الاستيراد') }}
                </x-button>
            </div>
        </div>
    @endif

    {{-- Step 4: processing --}}
    @if ($importBatch->status === App\Enums\ImportBatchStatus::Processing)
        <div wire:poll.2s="refreshProgress" class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
            <x-heading size="md">{{ __('جارٍ الاستيراد...') }}</x-heading>

            @php $total = max(1, $importBatch->total_rows ?? 1); @endphp
            <div class="mt-4 h-3 w-full rounded-full bg-surface-container-high">
                <div class="h-3 rounded-full bg-primary transition-all" style="width: {{ min(100, ($importBatch->processed_rows / $total) * 100) }}%"></div>
            </div>
            <p class="mt-2 text-body-md text-on-surface-variant">{{ number_format($importBatch->processed_rows) }} / {{ number_format($importBatch->total_rows ?? 0) }}</p>
        </div>
    @endif

    {{-- Step 5: report --}}
    @if ($importBatch->status === App\Enums\ImportBatchStatus::Completed || $importBatch->status === App\Enums\ImportBatchStatus::Failed)
        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
            <x-heading size="md">{{ __('تقرير الاستيراد') }}</x-heading>

            @if ($importBatch->status === App\Enums\ImportBatchStatus::Failed)
                <p class="mt-2 text-body-md text-error">{{ __('توقف الاستيراد بسبب خطأ غير متوقع. راجع سجلات الخادم أو أعد المحاولة بملف جديد.') }}</p>
            @endif

            <dl class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                <div>
                    <dt class="text-caption text-on-surface-variant">{{ __('إجمالي الصفوف') }}</dt>
                    <dd class="text-headline-md text-primary">{{ number_format($importBatch->total_rows ?? 0) }}</dd>
                </div>
                <div>
                    <dt class="text-caption text-on-surface-variant">{{ __('تم استيرادها') }}</dt>
                    <dd class="text-headline-md text-tertiary">{{ number_format($importBatch->imported_count) }}</dd>
                </div>
                <div>
                    <dt class="text-caption text-on-surface-variant">{{ __('مكررة') }}</dt>
                    <dd class="text-headline-md text-secondary">{{ number_format($importBatch->duplicate_count) }}</dd>
                </div>
                <div>
                    <dt class="text-caption text-on-surface-variant">{{ __('أخطاء بيانات') }}</dt>
                    <dd class="text-headline-md text-error">{{ number_format($importBatch->validation_failed_count) }}</dd>
                </div>
                <div>
                    <dt class="text-caption text-on-surface-variant">{{ __('بدون تصنيف') }}</dt>
                    <dd class="text-headline-md text-on-surface">{{ number_format($importBatch->unknown_category_count) }}</dd>
                </div>
                <div>
                    <dt class="text-caption text-on-surface-variant">{{ __('بدون مؤلف') }}</dt>
                    <dd class="text-headline-md text-on-surface">{{ number_format($importBatch->unknown_author_count) }}</dd>
                </div>
                <div>
                    <dt class="text-caption text-on-surface-variant">{{ __('أخطاء أخرى') }}</dt>
                    <dd class="text-headline-md text-error">{{ number_format($importBatch->error_count) }}</dd>
                </div>
            </dl>

            @if ($importBatch->rowErrors()->exists())
                <div class="mt-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-headline-md text-on-surface">{{ __('الصفوف التي لم تُستورد') }}</h4>
                        <a href="{{ route('admin.import.errors', $importBatch) }}" class="font-bold text-primary hover:underline">{{ __('تنزيل كملف CSV') }}</a>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-right">
                            <thead>
                                <tr class="border-b border-outline-variant text-label-bold text-on-surface-variant">
                                    <th class="px-3 py-2">{{ __('الصف') }}</th>
                                    <th class="px-3 py-2">{{ __('النوع') }}</th>
                                    <th class="px-3 py-2">{{ __('التفاصيل') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                @foreach ($importBatch->rowErrors()->where('type', '!=', 'unknown_category')->where('type', '!=', 'unknown_author')->orderBy('row_number')->take(200)->get() as $rowError)
                                    <tr>
                                        <td class="px-3 py-2 text-body-md">{{ $rowError->row_number }}</td>
                                        <td class="px-3 py-2 text-body-md">{{ $rowError->type->label() }}</td>
                                        <td class="px-3 py-2 text-caption text-on-surface-variant">{{ $rowError->message }} — {{ $rowError->row_data['title'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
