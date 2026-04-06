<?php

namespace App\Livewire\School\Admin;

use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\Payments\PaymentStatusCalculator;
use App\Services\Schools\SchoolPlanLimitService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Spatie\Permission\Models\Role;

#[Title('School Admin Dashboard')]
class Dashboard extends SchoolAdminPage
{
    #[Url(as: 'current-term', history: true)]
    public string $currentTermPanel = '0';

    public bool $showParentModal = false;

    public bool $showClassModal = false;

    public bool $showPaymentModal = false;

    public bool $showCurrentTermModal = false;

    public string $parentName = '';

    public string $parentEmail = '';

    public string $parentPassword = '';

    public string $parentPasswordConfirmation = '';

    public string $parentStatus = 'active';

    public string $className = '';

    public string $classSection = '';

    public string $classCode = '';

    public string $classCapacity = '';

    public string $classStatus = 'active';

    public string $paymentStudentId = '';

    public string $paymentParentUserId = '';

    public string $paymentType = '';

    public string $paymentAcademicYear = '';

    public string $paymentTerm = 'first';

    public string $paymentAmountDue = '';

    public string $paymentAmountPaid = '0';

    public string $paymentNotes = '';

    public string $currentAcademicYear = '';

    public string $currentTerm = 'first';

    public function mount(string $slug): void
    {
        parent::mount($slug);

        if ($this->currentTermPanel === '1') {
            $this->openCurrentTermModal();
        }
    }

    public function createParent(): void
    {
        $this->authorize('update', $this->currentSchool());
        $this->prepareDashboardModal();
        $this->resetParentForm();
        $this->showParentModal = true;
    }

    public function closeParentModal(): void
    {
        $this->showParentModal = false;
        $this->resetValidation();
        $this->resetParentForm();
    }

    public function createClass(): void
    {
        $this->authorize('update', $this->currentSchool());
        $this->prepareDashboardModal();
        $this->resetClassForm();
        $this->showClassModal = true;
    }

    public function createPayment(): void
    {
        $this->authorize('create', [Payment::class, $this->currentSchool()]);
        $this->prepareDashboardModal();
        $this->resetPaymentForm();
        $this->showPaymentModal = true;
    }

    public function openCurrentTermModal(): void
    {
        $school = $this->currentSchool();
        $this->authorize('update', $school);
        $this->prepareDashboardModal();
        $this->currentTermPanel = '1';
        $this->resetValidation();
        $this->currentAcademicYear = $school->currentAcademicYear();
        $this->currentTerm = $school->currentTerm();
        $this->showCurrentTermModal = true;
    }

    public function closeClassModal(): void
    {
        $this->showClassModal = false;
        $this->resetValidation();
        $this->resetClassForm();
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->resetValidation();
        $this->resetPaymentForm();
    }

    public function closeCurrentTermModal(): void
    {
        $this->currentTermPanel = '0';
        $this->showCurrentTermModal = false;
        $this->resetValidation();
        $this->resetCurrentTermForm();
    }

    public function updatingPaymentStudentId(): void
    {
        $this->paymentParentUserId = '';
        $this->resetValidation();
    }

    public function saveParent(): void
    {
        $school = $this->currentSchool();
        $this->authorize('update', $school);
        $this->normalizeParentFormFields();
        $validated = $this->validate($this->parentRules());
        app(SchoolPlanLimitService::class)->ensureCanAddParent($school, 'parentName');

        DB::transaction(function () use ($school, $validated): void {
            $parentRole = Role::findOrCreate('parent', 'web');
            app(SchoolPlanLimitService::class)->ensureCanAddParent($school, 'parentName');

            $parent = User::query()->create([
                'school_id' => $school->id,
                'name' => $validated['parentName'],
                'email' => $validated['parentEmail'],
                'password' => $validated['parentPassword'],
                'status' => $validated['parentStatus'],
            ]);

            $parent->forceFill([
                'email_verified_at' => now(),
            ])->save();

            $parent->assignRole($parentRole);
        });

        session()->flash('status', 'Parent created successfully. They can log in with '.$validated['parentEmail'].' and the password entered in the form.');

        $this->closeParentModal();
    }

    public function saveClass(): void
    {
        $school = $this->currentSchool();
        $this->authorize('update', $school);
        $this->normalizeClassFormFields();
        $validated = $this->validate($this->classRules($school));

        SchoolClass::query()->create([
            'school_id' => $school->id,
            'name' => $validated['className'],
            'section' => $validated['classSection'] ?? '',
            'code' => $validated['classCode'] ?: null,
            'capacity' => $validated['classCapacity'] !== null ? (int) $validated['classCapacity'] : null,
            'status' => $validated['classStatus'],
        ]);

        session()->flash('status', 'Class created successfully.');

        $this->closeClassModal();
    }

    public function savePayment(PaymentStatusCalculator $calculator): void
    {
        $school = $this->currentSchool();
        $this->authorize('create', [Payment::class, $school]);
        $this->normalizePaymentFormFields();
        $validated = $this->validate($this->paymentRules());

        $student = $this->paymentStudentOptionsQuery()
            ->whereKey((int) $validated['paymentStudentId'])
            ->firstOrFail();

        $amounts = $calculator->fromAmounts(
            (float) $validated['paymentAmountDue'],
            (float) $validated['paymentAmountPaid'],
        );

        Payment::query()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'parent_user_id' => $validated['paymentParentUserId'] !== '' ? (int) $validated['paymentParentUserId'] : null,
            'reference' => $this->generatePaymentReference(),
            'payment_type' => $validated['paymentType'],
            'academic_year' => $validated['paymentAcademicYear'],
            'term' => $validated['paymentTerm'],
            'amount_due' => $amounts['amount_due'],
            'amount_paid' => $amounts['amount_paid'],
            'balance' => $amounts['balance'],
            'currency' => 'NGN',
            'status' => $amounts['status'],
            'payment_method' => null,
            'paid_at' => $amounts['status'] === 'paid' ? now() : null,
            'notes' => $validated['paymentNotes'] !== '' ? $validated['paymentNotes'] : null,
        ]);

        session()->flash('status', 'Payment record created successfully.');

        $this->closePaymentModal();
    }

    public function saveCurrentTerm(): void
    {
        $school = $this->currentSchool();
        $this->authorize('update', $school);
        $this->normalizeCurrentTermFormFields();

        $validated = $this->validate([
            'currentAcademicYear' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'currentTerm' => ['required', Rule::in(['first', 'second', 'third'])],
        ]);

        $school->update([
            'current_academic_year' => $validated['currentAcademicYear'],
            'current_term' => $validated['currentTerm'],
        ]);

        $this->resolvedSchool = $school->fresh();

        session()->flash('status', 'Current term updated successfully.');

        $this->closeCurrentTermModal();
    }

    public function render(PaymentStatusCalculator $calculator): View
    {
        $school = $this->currentSchool();
        $this->authorize('view', $school);

        $teachersQuery = $this->usersByRoleQuery($school, 'teacher');
        $parentsQuery = $this->usersByRoleQuery($school, 'parent');
        $outstandingPaymentsQuery = Payment::query()
            ->where('school_id', $school->id)
            ->whereIn('status', ['unpaid', 'partial']);
        $paymentPreview = $calculator->fromAmounts(
            (float) ($this->paymentAmountDue !== '' ? $this->paymentAmountDue : 0),
            (float) ($this->paymentAmountPaid !== '' ? $this->paymentAmountPaid : 0),
        );

        $recentStudents = Student::query()
            ->where('school_id', $school->id)
            ->with('schoolClass:id,name,section')
            ->latest()
            ->take(5)
            ->get([
                'id',
                'school_id',
                'school_class_id',
                'first_name',
                'last_name',
                'admission_number',
                'status',
                'created_at',
            ]);

        $paymentSummary = [
            [
                'label' => 'Unpaid records',
                'value' => number_format((clone $outstandingPaymentsQuery)->where('status', 'unpaid')->count()),
                'hint' => 'Balances with no payment made yet',
            ],
            [
                'label' => 'Partial records',
                'value' => number_format(
                    Payment::query()
                        ->where('school_id', $school->id)
                        ->where('status', 'partial')
                        ->count(),
                ),
                'hint' => 'Balances that are partly settled',
            ],
            [
                'label' => 'Active classes',
                'value' => number_format(
                    SchoolClass::query()
                        ->where('school_id', $school->id)
                        ->where('status', 'active')
                        ->count(),
                ),
                'hint' => 'Classes currently open to students',
            ],
        ];

        $moduleLinks = [
            [
                'label' => 'Teachers',
                'description' => 'Prepare teacher onboarding, staffing, and class assignments.',
                'route' => $this->schoolRoute('school.admin.teachers.index'),
            ],
            [
                'label' => 'Parents',
                'description' => 'Organize parent accounts and guardianship records.',
                'route' => $this->schoolRoute('school.admin.parents.index'),
            ],
            [
                'label' => 'Classes',
                'description' => 'Structure academic groups before student operations begin.',
                'route' => $this->schoolRoute('school.admin.classes.index'),
            ],
            [
                'label' => 'Students',
                'description' => 'Track enrolled learners and their class placement.',
                'route' => $this->schoolRoute('school.admin.students.index'),
            ],
            [
                'label' => 'Assignments',
                'description' => 'Link students to parents with clean guardian records.',
                'route' => $this->schoolRoute('school.admin.assignments.index'),
            ],
            [
                'label' => 'Payments',
                'description' => 'Monitor pending and completed payment activity.',
                'route' => $this->schoolRoute('school.admin.payments.index'),
            ],
        ];

        $paymentStudents = collect();
        $paymentParentOptions = collect();

        if ($this->showPaymentModal) {
            $paymentStudents = $this->paymentStudentOptionsQuery()
                ->with('schoolClass:id,name,section')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(['id', 'school_class_id', 'first_name', 'last_name', 'admission_number']);

            $paymentParentOptions = $this->linkedPaymentParentOptionsQuery()
                ->orderBy('name')
                ->get(['users.id', 'users.name', 'users.email']);
        }

        return view('livewire.school.admin.dashboard', [
            'school' => $school,
            'stats' => [
                [
                    'label' => 'Total students',
                    'value' => number_format(
                        Student::query()
                            ->where('school_id', $school->id)
                            ->count(),
                    ),
                    'hint' => 'Students currently stored for this school',
                ],
                [
                    'label' => 'Total teachers',
                    'value' => number_format((clone $teachersQuery)->count()),
                    'hint' => 'Teacher accounts assigned to this tenant',
                ],
                [
                    'label' => 'Total parents',
                    'value' => number_format((clone $parentsQuery)->count()),
                    'hint' => 'Parent accounts linked to this tenant',
                ],
                [
                    'label' => 'Outstanding payments',
                    'value' => 'NGN '.number_format((float) (clone $outstandingPaymentsQuery)->sum('balance'), 2),
                    'hint' => 'Total remaining balance for this school',
                ],
                [
                    'label' => 'Current term',
                    'value' => $school->currentTermLabel(),
                    'hint' => $school->currentAcademicYear(),
                ],
                [
                    'label' => 'Current plan',
                    'value' => $this->resolvedPlanLabel($school),
                    'hint' => 'Subscription plan for this school',
                ],
            ],
            'paymentSummary' => $paymentSummary,
            'moduleLinks' => $moduleLinks,
            'recentStudents' => $recentStudents,
            'paymentStudents' => $paymentStudents,
            'paymentParentOptions' => $paymentParentOptions,
            'paymentPreview' => $paymentPreview,
        ])->layout('layouts.school.admin');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function parentRules(): array
    {
        return [
            'parentName' => ['required', 'string', 'max:255'],
            'parentEmail' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'parentPassword' => [
                'required',
                'same:parentPasswordConfirmation',
                Password::min(8)->mixedCase()->numbers(),
            ],
            'parentPasswordConfirmation' => ['required'],
            'parentStatus' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function classRules(School $school): array
    {
        return [
            'className' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($school): void {
                    $section = $this->normalizeNullableString($this->classSection);

                    $exists = SchoolClass::query()
                        ->where('school_id', $school->id)
                        ->where('name', $value)
                        ->where(function ($query) use ($section): void {
                            if ($section === null) {
                                $query->whereNull('section')->orWhere('section', '');

                                return;
                            }

                            $query->where('section', $section);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('A class with this name and section already exists in this school.');
                    }
                },
            ],
            'classSection' => ['nullable', 'string', 'max:255'],
            'classCode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('school_classes', 'code')->where(
                    fn ($query) => $query->where('school_id', $school->id),
                ),
            ],
            'classCapacity' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'classStatus' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function paymentRules(): array
    {
        return [
            'paymentStudentId' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(
                    fn ($query) => $query->where('school_id', $this->currentSchool()->id),
                ),
            ],
            'paymentParentUserId' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $validParent = $this->linkedPaymentParentOptionsQuery()
                        ->where('users.id', (int) $value)
                        ->exists();

                    if (! $validParent) {
                        $fail('The selected parent is invalid for the chosen student.');
                    }
                },
            ],
            'paymentType' => ['required', 'string', 'max:255'],
            'paymentAcademicYear' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'paymentTerm' => ['required', Rule::in(['first', 'second', 'third'])],
            'paymentAmountDue' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'paymentAmountPaid' => ['required', 'numeric', 'min:0', 'lte:paymentAmountDue'],
            'paymentNotes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function resetParentForm(): void
    {
        $this->parentName = '';
        $this->parentEmail = '';
        $this->parentPassword = '';
        $this->parentPasswordConfirmation = '';
        $this->parentStatus = 'active';
    }

    protected function resetClassForm(): void
    {
        $this->className = '';
        $this->classSection = '';
        $this->classCode = '';
        $this->classCapacity = '';
        $this->classStatus = 'active';
    }

    protected function resetPaymentForm(): void
    {
        $this->paymentStudentId = '';
        $this->paymentParentUserId = '';
        $this->paymentType = '';
        $this->paymentAcademicYear = $this->currentSchool()->currentAcademicYear();
        $this->paymentTerm = $this->currentSchool()->currentTerm();
        $this->paymentAmountDue = '';
        $this->paymentAmountPaid = '0';
        $this->paymentNotes = '';
    }

    protected function resetCurrentTermForm(): void
    {
        $this->currentAcademicYear = $this->currentSchool()->currentAcademicYear();
        $this->currentTerm = $this->currentSchool()->currentTerm();
    }

    protected function normalizeParentFormFields(): void
    {
        $this->parentName = trim($this->parentName);
        $this->parentEmail = strtolower(trim($this->parentEmail));
    }

    protected function normalizeClassFormFields(): void
    {
        $this->className = trim($this->className);
        $this->classSection = $this->normalizeNullableString($this->classSection) ?? '';
        $this->classCode = $this->normalizeNullableString($this->classCode) ?? '';
        $this->classCapacity = $this->normalizeNullableString($this->classCapacity) ?? '';
    }

    protected function normalizePaymentFormFields(): void
    {
        $this->paymentStudentId = trim($this->paymentStudentId);
        $this->paymentParentUserId = trim($this->paymentParentUserId);
        $this->paymentType = trim($this->paymentType);
        $this->paymentAcademicYear = trim($this->paymentAcademicYear);
        $this->paymentNotes = trim($this->paymentNotes);
    }

    protected function normalizeCurrentTermFormFields(): void
    {
        $this->currentAcademicYear = trim($this->currentAcademicYear);
    }

    protected function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    protected function resolvedPlanLabel(School $school): string
    {
        $plan = strtolower(trim((string) $school->plan));

        return in_array($plan, ['free', 'basic', 'premium'], true)
            ? ucfirst($plan)
            : 'Free';
    }

    protected function paymentStudentOptionsQuery(): Builder
    {
        return Student::query()
            ->where('school_id', $this->currentSchool()->id);
    }

    protected function linkedPaymentParentOptionsQuery(): Builder
    {
        if ($this->paymentStudentId === '') {
            return User::query()->whereRaw('1 = 0');
        }

        return $this->usersByRoleQuery($this->currentSchool(), 'parent')
            ->select('users.*')
            ->join('parent_student', 'parent_student.parent_user_id', '=', 'users.id')
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['school_admin', 'super_admin']))
            ->where('parent_student.school_id', $this->currentSchool()->id)
            ->where('parent_student.student_id', (int) $this->paymentStudentId)
            ->distinct();
    }

    protected function generatePaymentReference(): string
    {
        do {
            $reference = 'PAY-'.Str::upper(Str::random(10));
        } while (Payment::query()->where('reference', $reference)->exists());

        return $reference;
    }

    protected function defaultAcademicYear(): string
    {
        return $this->currentSchool()->currentAcademicYear();
    }

    protected function prepareDashboardModal(): void
    {
        $this->showParentModal = false;
        $this->showClassModal = false;
        $this->showPaymentModal = false;
        $this->showCurrentTermModal = false;
        $this->resetValidation();
    }
}
