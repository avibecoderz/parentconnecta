<?php

namespace App\Livewire\School\Admin\Payments;

use App\Livewire\School\Admin\SchoolAdminPage;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\Payments\PaymentStatusCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Title('Payments')]
class Index extends SchoolAdminPage
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = 'all';

    #[Url(as: 'create', history: true, except: '0')]
    public string $create = '0';

    public bool $showPaymentModal = false;

    public ?int $editingPaymentId = null;

    public string $studentId = '';

    public string $parentUserId = '';

    public string $paymentType = '';

    public string $academicYear = '';

    public string $term = 'first';

    public string $amountDue = '';

    public string $amountPaid = '0';

    public string $notes = '';

    public function mount(string $slug): void
    {
        parent::mount($slug);
        $this->academicYear = $this->defaultAcademicYear();
        $this->term = $this->currentSchool()->currentTerm();

        if ($this->shouldOpenCreateModal()) {
            $this->createPayment();
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

    public function updatingStudentId(): void
    {
        $this->parentUserId = '';
        $this->resetValidation();
    }

    public function createPayment(): void
    {
        $this->authorize('create', [Payment::class, $this->currentSchool()]);
        $this->create = '1';
        $this->resetValidation();
        $this->resetForm();
        $this->showPaymentModal = true;
    }

    public function editPayment(int $paymentId): void
    {
        $payment = $this->paymentsQueryBase()->findOrFail($paymentId);
        $this->authorize('update', $payment);

        $this->resetValidation();
        $this->editingPaymentId = $payment->id;
        $this->studentId = (string) $payment->student_id;
        $this->parentUserId = $payment->parent_user_id !== null ? (string) $payment->parent_user_id : '';
        $this->paymentType = $payment->payment_type;
        $this->academicYear = (string) $payment->academic_year;
        $this->term = (string) $payment->term;
        $this->amountDue = number_format((float) $payment->amount_due, 2, '.', '');
        $this->amountPaid = number_format((float) $payment->amount_paid, 2, '.', '');
        $this->notes = (string) ($payment->notes ?? '');
        $this->showPaymentModal = true;
    }

    public function savePayment(PaymentStatusCalculator $calculator): void
    {
        $this->normalizeFormFields();
        $validated = $this->validate($this->rules());
        $school = $this->currentSchool();

        if ($this->editingPaymentId !== null) {
            $this->authorize('update', $this->paymentsQueryBase()->findOrFail($this->editingPaymentId));
        } else {
            $this->authorize('create', [Payment::class, $school]);
        }

        $student = $this->studentOptionsQuery()
            ->whereKey((int) $validated['studentId'])
            ->firstOrFail();

        $amounts = $calculator->fromAmounts(
            (float) $validated['amountDue'],
            (float) $validated['amountPaid'],
        );

        $payload = [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'parent_user_id' => $validated['parentUserId'] !== '' ? (int) $validated['parentUserId'] : null,
            'payment_type' => $validated['paymentType'],
            'academic_year' => $validated['academicYear'],
            'term' => $validated['term'],
            'amount_due' => $amounts['amount_due'],
            'amount_paid' => $amounts['amount_paid'],
            'balance' => $amounts['balance'],
            'status' => $amounts['status'],
            'currency' => 'NGN',
            'payment_method' => null,
            'paid_at' => $amounts['status'] === 'paid' ? now() : null,
            'notes' => $validated['notes'] !== '' ? $validated['notes'] : null,
        ];

        if ($this->editingPaymentId !== null) {
            $payment = $this->paymentsQueryBase()->findOrFail($this->editingPaymentId);
            $payload['reference'] = $payment->reference;
            $payload['paid_at'] = $amounts['status'] === 'paid'
                ? ($payment->paid_at ?? now())
                : null;

            $payment->update($payload);

            session()->flash('status', 'Payment record updated successfully.');
        } else {
            $payload['reference'] = $this->generateReference();
            Payment::query()->create($payload);

            session()->flash('status', 'Payment record created successfully.');
        }

        $this->closePaymentModal();
    }

    public function deletePayment(int $paymentId): void
    {
        $payment = $this->paymentsQueryBase()->findOrFail($paymentId);
        $this->authorize('delete', $payment);
        $reference = $payment->reference;

        $payment->delete();

        session()->flash('status', "Payment {$reference} was deleted successfully.");
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->create = '0';
        $this->resetForm();
    }

    public function render(PaymentStatusCalculator $calculator): View
    {
        $school = $this->currentSchool();
        $this->authorize('viewAny', [Payment::class, $school]);
        $paymentsQuery = $this->filteredPaymentsQuery();
        $preview = $calculator->fromAmounts(
            (float) ($this->amountDue !== '' ? $this->amountDue : 0),
            (float) ($this->amountPaid !== '' ? $this->amountPaid : 0),
        );

        return view('livewire.school.admin.payments.index', [
            'school' => $school,
            'payments' => $paymentsQuery->paginate(10),
            'students' => $this->studentOptionsQuery()
                ->with('schoolClass:id,name,section')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(['id', 'school_class_id', 'first_name', 'last_name', 'admission_number']),
            'parentOptions' => $this->linkedParentOptionsQuery()
                ->orderBy('name')
                ->get(['users.id', 'users.name', 'users.email']),
            'statusOptions' => ['all', 'unpaid', 'partial', 'paid'],
            'metrics' => [
                [
                    'label' => 'Payment records',
                    'value' => number_format((clone $this->paymentsQueryBase())->count()),
                    'hint' => 'All payment tracking rows for this school',
                ],
                [
                    'label' => 'Unpaid records',
                    'value' => number_format((clone $this->paymentsQueryBase())->where('status', 'unpaid')->count()),
                    'hint' => 'Balances with no payment made yet',
                ],
                [
                    'label' => 'Partial records',
                    'value' => number_format((clone $this->paymentsQueryBase())->where('status', 'partial')->count()),
                    'hint' => 'Balances that are partly settled',
                ],
                [
                    'label' => 'Outstanding balance',
                    'value' => 'NGN '.number_format((float) (clone $this->paymentsQueryBase())->sum('balance'), 2),
                    'hint' => 'Total remaining balance across all tracked payments',
                ],
            ],
            'preview' => $preview,
        ])->layout('layouts.school.admin');
    }

    protected function rules(): array
    {
        return [
            'studentId' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(
                    fn ($query) => $query->where('school_id', $this->currentSchool()->id),
                ),
            ],
            'parentUserId' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $validParent = $this->linkedParentOptionsQuery()
                        ->where('users.id', (int) $value)
                        ->exists();

                    if (! $validParent) {
                        $fail('The selected parent is invalid for the chosen student.');
                    }
                },
            ],
            'paymentType' => ['required', 'string', 'max:255'],
            'academicYear' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'term' => ['required', Rule::in(['first', 'second', 'third'])],
            'amountDue' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'amountPaid' => ['required', 'numeric', 'min:0', 'lte:amountDue'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function resetForm(): void
    {
        $this->editingPaymentId = null;
        $this->studentId = '';
        $this->parentUserId = '';
        $this->paymentType = '';
        $this->academicYear = $this->defaultAcademicYear();
        $this->term = $this->currentSchool()->currentTerm();
        $this->amountDue = '';
        $this->amountPaid = '0';
        $this->notes = '';
    }

    protected function normalizeFormFields(): void
    {
        $this->paymentType = trim($this->paymentType);
        $this->academicYear = trim($this->academicYear);
        $this->notes = trim($this->notes);
    }

    protected function paymentsQueryBase(): Builder
    {
        return Payment::query()
            ->where('school_id', $this->currentSchool()->id)
            ->with([
                'student:id,school_class_id,first_name,last_name,admission_number',
                'student.schoolClass:id,name,section',
                'parent:id,name,email',
            ]);
    }

    protected function filteredPaymentsQuery(): Builder
    {
        return $this->paymentsQueryBase()
            ->when(
                $this->statusFilter !== 'all',
                fn (Builder $query) => $query->where('status', $this->statusFilter),
            )
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('reference', 'like', "%{$search}%")
                        ->orWhere('payment_type', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('admission_number', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    protected function studentOptionsQuery(): Builder
    {
        return Student::query()
            ->where('school_id', $this->currentSchool()->id);
    }

    protected function linkedParentOptionsQuery(): Builder
    {
        if ($this->studentId === '') {
            return User::query()->whereRaw('1 = 0');
        }

        return $this->usersByRoleQuery($this->currentSchool(), 'parent')
            ->select('users.*')
            ->join('parent_student', 'parent_student.parent_user_id', '=', 'users.id')
            ->whereDoesntHave('roles', fn (Builder $query) => $query->whereIn('name', ['school_admin', 'super_admin']))
            ->where('parent_student.school_id', $this->currentSchool()->id)
            ->where('parent_student.student_id', (int) $this->studentId)
            ->distinct();
    }

    protected function generateReference(): string
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

    protected function shouldOpenCreateModal(): bool
    {
        return $this->create === '1';
    }
}
