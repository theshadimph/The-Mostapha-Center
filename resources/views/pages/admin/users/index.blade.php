<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] class extends Component
{
    use PasswordValidationRules, ProfileValidationRules, WithPagination;

    public string $search = '';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('manage-users');

        $this->reset(['editingId', 'name', 'email', 'password', 'password_confirmation', 'role']);
        $this->showFormModal = true;
    }

    public function edit(User $user): void
    {
        Gate::authorize('manage-users');

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = $user->role?->value ?? '';
        $this->showFormModal = true;
    }

    public function save(): void
    {
        Gate::authorize('manage-users');

        $user = $this->editingId ? User::findOrFail($this->editingId) : new User;

        $rules = [
            ...$this->profileRules($this->editingId),
            // The role select's "no admin access" option posts '' (not
            // null — Livewire-bound string properties can't be null), so ''
            // must be an explicitly allowed value here, same as updateRole().
            'role' => ['string', Rule::in(['', ...array_map(fn (UserRole $r) => $r->value, UserRole::cases())])],
        ];

        if (! $this->editingId) {
            $rules['password'] = $this->passwordRules();
        }

        $validated = $this->validate($rules, attributes: [
            'name' => __('الاسم'),
            'email' => __('البريد الإلكتروني'),
            'password' => __('كلمة المرور'),
            'role' => __('الصلاحية'),
        ]);

        if ($this->editingId && $user->is(auth()->user()) && $validated['role'] === '') {
            $this->dispatch('toast-show', variant: 'error', text: __('لا يمكنك إزالة صلاحياتك الخاصة.'));

            return;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'] !== '' ? UserRole::from($validated['role']) : null;

        if (! $this->editingId) {
            $user->password = $this->password;
            $user->email_verified_at = now();
        }

        $user->save();

        $this->showFormModal = false;
        $this->dispatch('toast-show', variant: 'success', text: __('تم حفظ المستخدم بنجاح.'));
    }

    public function updateRole(User $user, string $role): void
    {
        Gate::authorize('manage-users');

        if ($user->is(auth()->user()) && $role === '') {
            $this->dispatch('toast-show', variant: 'error', text: __('لا يمكنك إزالة صلاحياتك الخاصة.'));

            return;
        }

        $user->update(['role' => $role !== '' ? UserRole::from($role) : null]);

        $this->dispatch('toast-show', variant: 'success', text: __('تم تحديث الصلاحية.'));
    }

    public function toggleDisabled(User $user): void
    {
        Gate::authorize('manage-users');

        if ($user->is(auth()->user())) {
            $this->dispatch('toast-show', variant: 'error', text: __('لا يمكنك تعطيل حسابك الخاص.'));

            return;
        }

        $user->update(['disabled_at' => $user->isDisabled() ? null : now()]);

        $this->dispatch('toast-show', variant: 'success', text: $user->isDisabled() ? __('تم تعطيل الحساب.') : __('تم تفعيل الحساب.'));
    }

    public function with(): array
    {
        return [
            'users' => User::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(15),
            'roles' => UserRole::all(),
        ];
    }
}; ?>

<div class="flex flex-col gap-gutter">
    <div class="flex items-center justify-between">
        <x-heading size="lg">{{ __('المستخدمون') }}</x-heading>
        <x-button wire:click="create" variant="primary">
            <x-icon name="add" class="text-lg" />
            {{ __('إضافة مستخدم') }}
        </x-button>
    </div>

    <x-input wire:model.live.debounce.300ms="search" icon="search" :placeholder="__('ابحث بالاسم أو البريد الإلكتروني...')" />

    <div class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="border-b border-outline-variant bg-surface-container-low text-label-bold text-on-surface-variant">
                        <th class="px-6 py-4">{{ __('الاسم') }}</th>
                        <th class="px-6 py-4">{{ __('البريد الإلكتروني') }}</th>
                        <th class="px-6 py-4">{{ __('الصلاحية') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('الحالة') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('إجراءات') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($users as $user)
                        <tr class="hover:bg-surface-container-low">
                            <td class="px-6 py-4 font-bold text-primary">
                                {{ $user->name }}
                                @if ($user->is(auth()->user()))
                                    <span class="text-caption text-on-surface-variant">({{ __('أنت') }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-body-md" dir="ltr">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <select
                                    wire:change="updateRole({{ $user->id }}, $event.target.value)"
                                    class="h-10 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 text-body-md outline-none focus:border-primary"
                                >
                                    <option value="" @selected(! $user->role)>{{ __('بدون صلاحيات إدارية') }}</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($user->isDisabled())
                                    <x-badge variant="error">{{ __('معطّل') }}</x-badge>
                                @else
                                    <x-badge variant="success">{{ __('نشط') }}</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" wire:click="edit({{ $user->id }})" class="rounded-full p-2 text-primary hover:bg-primary/10">
                                        <x-icon name="edit" />
                                    </button>
                                    @unless ($user->is(auth()->user()))
                                        <button
                                            type="button"
                                            wire:click="toggleDisabled({{ $user->id }})"
                                            wire:confirm="{{ $user->isDisabled() ? __('تفعيل هذا الحساب؟') : __('تعطيل هذا الحساب؟ لن يتمكن صاحبه من تسجيل الدخول.') }}"
                                            class="rounded-full p-2 {{ $user->isDisabled() ? 'text-tertiary hover:bg-tertiary/10' : 'text-error hover:bg-error/10' }}"
                                        >
                                            <x-icon :name="$user->isDisabled() ? 'lock_open' : 'lock'" />
                                        </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-body-md text-on-surface-variant">{{ __('لا يوجد مستخدمون.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $users->links() }}

    <x-modal name="user-form" wire-model="showFormModal">
        <form wire:submit="save" class="flex flex-col gap-4">
            <x-heading size="md">{{ $editingId ? __('تعديل المستخدم') : __('إضافة مستخدم') }}</x-heading>

            <x-input wire:model="name" :label="__('الاسم')" required />
            <x-input wire:model="email" type="email" :label="__('البريد الإلكتروني')" dir="ltr" required />

            @unless ($editingId)
                <x-input wire:model="password" type="password" :label="__('كلمة المرور')" required />
                <x-input wire:model="password_confirmation" type="password" :label="__('تأكيد كلمة المرور')" required />
            @endunless

            <x-select wire:model="role" :label="__('الصلاحية')">
                <option value="">{{ __('بدون صلاحيات إدارية') }}</option>
                @foreach ($roles as $roleOption)
                    <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                @endforeach
            </x-select>

            <div class="mt-2 flex justify-end gap-2">
                <x-button type="button" variant="ghost" wire:click="$set('showFormModal', false)">{{ __('إلغاء') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('حفظ') }}</x-button>
            </div>
        </form>
    </x-modal>
</div>
