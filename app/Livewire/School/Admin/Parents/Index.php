<?php

namespace App\Livewire\School\Admin\Parents;

use App\Livewire\School\Admin\SchoolAdminPage;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\Schools\SchoolPlanLimitService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Parents')]
class Index extends SchoolAdminPage
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = 'all';

    #[Url(as: 'create', history: true, except: '0')]
    public string $create = '0';

    public bool $showParentModal = false;

    public bool $showDeleteModal = false;

    public bool $showPlanLimitModal = false;

    public ?int $editingParentId = null;

    public ?int $deletingParentId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $status = 'active';

    public string $planLimitMessage = '';

    public function mount(string $slug): void
    {
        parent::mount($slug);

        if ($this->shouldOpenCreateModal()) {
            $this->createParent();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function createParent(): void
    {
        $school = $this->currentSchool();
        $this->authorize('update', $school);

        try {
            $this->ensureParentCreationAllowedByPlan($school);
        } catch (ValidationException $exception) {
            if ($this->openPlanLimitModalFromValidationException($exception)) {
                $this->create = '0';
                $this->showParentModal = false;

                return;
            }

            throw $exception;
        }

        $this->create = '1';
        $this->resetValidation();
        $this->resetForm();
        $this->showParentModal = true;
    }

    public function editParent(int $parentId): void
    {
        $this->authorize('update', $this->currentSchool());
        $parent = $this->parentLookupQuery()
            ->findOrFail($parentId, ['id', 'name', 'email', 'status']);

        $this->resetValidation();
        $this->editingParentId = $parent->id;
        $this->name = $parent->name;
        $this->email = $parent->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->status = $parent->status;
        $this->showParentModal = true;
    }

    public function saveParent(): void
    {
        try {
            $school = $this->currentSchool();
            $this->authorize('update', $school);
            $this->normalizeFormFields();
            $validated = $this->validate($this->rules($school));

            if ($this->editingParentId === null) {
                $this->ensureParentCreationAllowedByPlan($school);
            }

            DB::transaction(function () use ($school, $validated): void {
                $parentRole = Role::findOrCreate('parent', 'web');

                if ($this->editingParentId !== null) {
                    $parent = $this->parentLookupQuery()->findOrFail($this->editingParentId);
                    $parent->fill([
                        'school_id' => $school->id,
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'status' => $validated['status'],
                    ]);

                    if (($validated['password'] ?? null) !== null && $validated['password'] !== '') {
                        $parent->password = $validated['password'];
                    }

                    $parent->save();

                    if (! $parent->hasRole($parentRole)) {
                        $parent->assignRole($parentRole);
                    }

                    session()->flash('status', 'Parent updated successfully.');

                    return;
                }

                $this->ensureParentCreationAllowedByPlan($school);

                $parent = User::query()->create([
                    'school_id' => $school->id,
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'status' => $validated['status'],
                ]);

                $parent->forceFill([
                    'email_verified_at' => now(),
                ])->save();

                $parent->assignRole($parentRole);

                session()->flash('status', 'Parent created successfully.');
            });

            $this->closeParentModal();
        } catch (ValidationException $exception) {
            if ($this->openPlanLimitModalFromValidationException($exception)) {
                return;
            }

            throw $exception;
        }
    }

    public function toggleParentStatus(int $parentId): void
    {
        $this->authorize('update', $this->currentSchool());
        $parent = $this->parentLookupQuery()->findOrFail($parentId);
        $nextStatus = $parent->status === 'active' ? 'inactive' : 'active';

        $parent->update([
            'status' => $nextStatus,
        ]);

        session()->flash(
            'status',
            $nextStatus === 'active'
                ? "{$parent->name} has been activated."
                : "{$parent->name} has been suspended.",
        );
    }

    public function confirmDelete(int $parentId): void
    {
        $this->authorize('update', $this->currentSchool());
        $this->deletingParentId = $this->parentLookupQuery()->findOrFail($parentId, ['id'])->id;
        $this->showDeleteModal = true;
    }

    public function deleteParent(): void
    {
        $this->authorize('update', $this->currentSchool());
        $parent = $this->parentLookupQuery()
            ->withCount(['children', 'payments'])
            ->with('roles:id,name')
            ->findOrFail($this->deletingParentId);

        $otherRoles = $parent->roles
            ->pluck('name')
            ->filter(fn (string $roleName): bool => $roleName !== 'parent')
            ->values();

        if ($otherRoles->isNotEmpty()) {
            $this->showDeleteModal = false;
            $this->deletingParentId = null;

            session()->flash(
                'error',
                $parent->name.' cannot be deleted here because this account also has the following role(s): '.$otherRoles->implode(', ').'.',
            );

            return;
        }

        $parentName = $parent->name;
        $linkedStudents = $parent->children_count;
        $paymentCount = $parent->payments_count;

        DB::transaction(function () use ($parent): void {
            $parent->delete();
        });

        $this->showDeleteModal = false;
        $this->deletingParentId = null;

        $message = "{$parentName} was deleted successfully.";

        if ($linkedStudents > 0 || $paymentCount > 0) {
            $details = [];

            if ($linkedStudents > 0) {
                $details[] = "{$linkedStudents} student links were removed";
            }

            if ($paymentCount > 0) {
                $details[] = "{$paymentCount} payment records were preserved and detached from the parent";
            }

            $message .= ' '.implode('; ', $details).'.';
        }

        session()->flash('status', $message);
    }

    public function closeParentModal(): void
    {
        $this->showParentModal = false;
        $this->create = '0';
        $this->resetValidation();
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingParentId = null;
    }

    public function closePlanLimitModal(): void
    {
        $this->showPlanLimitModal = false;
        $this->planLimitMessage = '';
    }

    public function render(): View
    {
        $school = $this->currentSchool();
        $this->authorize('view', $school);

        return view('livewire.school.admin.parents.index', [
            'school' => $school,
            'parents' => $this->parentsQuery()->paginate(10),
            'metrics' => [
                [
                    'label' => 'Parent accounts',
                    'value' => number_format($this->parentLookupQuery()->count()),
                    'hint' => 'All parent users under this school',
                ],
                [
                    'label' => 'Active parents',
                    'value' => number_format(
                        $this->parentLookupQuery()
                            ->where('status', 'active')
                            ->count(),
                    ),
                    'hint' => 'Parents currently able to access the portal',
                ],
                [
                    'label' => 'Linked students',
                    'value' => number_format(
                        Student::query()
                            ->where('school_id', $school->id)
                            ->whereHas('parents')
                            ->count(),
                    ),
                    'hint' => 'Students already linked to at least one parent',
                ],
                [
                    'label' => 'Primary guardians',
                    'value' => number_format(
                        (int) DB::table('parent_student')
                            ->where('school_id', $school->id)
                            ->where('is_primary', true)
                            ->count(),
                    ),
                    'hint' => 'Primary parent relationships marked in the system',
                ],
            ],
            'deletingParent' => $this->deletingParentId
                ? $this->parentLookupQuery()
                    ->withCount(['children', 'payments'])
                    ->with('roles:id,name')
                    ->find($this->deletingParentId, ['id', 'name', 'email'])
                : null,
        ])->layout('layouts.school.admin');
    }

    protected function rules(School $school): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingParentId),
            ],
            'password' => [
                Rule::requiredIf($this->editingParentId === null),
                'nullable',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function resetForm(): void
    {
        $this->editingParentId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->status = 'active';
    }

    protected function normalizeFormFields(): void
    {
        $this->name = trim($this->name);
        $this->email = strtolower(trim($this->email));
    }

    protected function parentsQuery(): Builder
    {
        return $this->parentLookupQuery()
            ->withCount(['children', 'payments'])
            ->when(
                $this->statusFilter !== 'all',
                fn (Builder $query) => $query->where('status', $this->statusFilter),
            )
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');
    }

    protected function parentLookupQuery(): Builder
    {
        return User::query()
            ->where('school_id', $this->currentSchool()->id)
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'parent'))
            ->whereDoesntHave('roles', fn (Builder $query) => $query->whereIn('name', ['school_admin', 'super_admin']));
    }

    protected function shouldOpenCreateModal(): bool
    {
        return $this->create === '1';
    }

    protected function ensureParentCreationAllowedByPlan(School $school): void
    {
        app(SchoolPlanLimitService::class)->ensureCanAddParent($school, 'name');
    }

    protected function openPlanLimitModalFromValidationException(ValidationException $exception): bool
    {
        $message = collect($exception->errors())
            ->flatten()
            ->first();

        if (! is_string($message)) {
            return false;
        }

        $normalizedMessage = strtolower($message);

        if (! str_contains($normalizedMessage, 'limit reached for') || ! str_contains($normalizedMessage, 'upgrade the school plan')) {
            return false;
        }

        $this->resetErrorBag();
        $this->planLimitMessage = $message;
        $this->showPlanLimitModal = true;

        return true;
    }
}
