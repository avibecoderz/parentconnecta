<?php

namespace App\Livewire\Admin\Schools;

use App\Models\School;
use App\Models\SchoolPaymentSetting;
use App\Models\User;
use App\Services\Schools\ProvisionSchool;
use App\Services\Schools\SchoolPlanLimitService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Manage Schools')]
class Index extends Component
{
    use WithPagination;

    /**
     * @var list<string>
     */
    protected const PLAN_OPTIONS = ['free', 'basic', 'premium'];

    protected ?bool $paymentSettingsTableAvailable = null;

    protected ?bool $schoolPlanColumnAvailable = null;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = 'all';

    #[Url(as: 'create', history: true)]
    public string $create = '0';

    public bool $showSchoolModal = false;

    public bool $showDeleteModal = false;

    public bool $showPaymentSettingsModal = false;

    public ?int $editingSchoolId = null;

    public ?int $schoolAdminId = null;

    public ?int $deletingSchoolId = null;

    public ?int $paymentSchoolId = null;

    public ?array $createdSchoolCredentials = null;

    public ?string $temporaryPassword = null;

    public ?string $temporaryPasswordSchoolName = null;

    public string $name = '';

    public string $slug = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $status = 'active';

    public string $plan = 'free';

    public string $timezone = 'Africa/Lagos';

    public string $adminName = '';

    public string $adminEmail = '';

    public string $adminPassword = '';

    public string $adminPassword_confirmation = '';

    public string $paymentGatewayName = 'paystack';

    public string $paystackPublicKey = '';

    public string $paystackSecretKey = '';

    public string $paystackMode = 'test';

    public bool $paymentSettingsActive = false;

    public string $merchantName = '';

    public string $merchantEmail = '';

    public string $merchantPhone = '';

    public bool $hasStoredSecretKey = false;

    public function mount(): void
    {
        if ($this->shouldOpenCreateModal()) {
            $this->openCreateModal();
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

    public function createSchool(): void
    {
        $this->create = '1';
        $this->openCreateModal();
    }

    public function editSchool(int $schoolId): void
    {
        $school = School::query()->findOrFail($schoolId);
        $admin = $this->resolveSchoolAdmin($school);

        $this->createdSchoolCredentials = null;
        $this->clearTemporaryPasswordNotice();
        $this->resetValidation();
        $this->editingSchoolId = $school->id;
        $this->schoolAdminId = $admin?->id;
        $this->name = $school->name;
        $this->slug = $school->slug;
        $this->email = $school->email ?? '';
        $this->phone = $school->phone ?? '';
        $this->address = $school->address ?? '';
        $this->status = $school->status;
        $this->plan = $this->normalizedStoredPlan($school->plan);
        $this->timezone = $school->timezone;
        $this->adminName = $admin?->name ?? '';
        $this->adminEmail = $admin?->email ?? '';
        $this->adminPassword = '';
        $this->adminPassword_confirmation = '';
        $this->showSchoolModal = true;
    }

    public function saveSchool(): void
    {
        if (! $this->hasSchoolPlanColumn()) {
            session()->flash('status', 'Plan management is unavailable because the schools plan column has not been migrated yet. Run php artisan migrate.');

            return;
        }

        $isEditing = $this->editingSchoolId !== null;
        $this->slug = $this->normalizedSlug();
        $this->plan = $this->normalizedSubmittedPlan($this->plan);
        $validated = $this->validate($this->rules());

        if (! $isEditing) {
            $provisionedSchool = app(ProvisionSchool::class)->create($validated);

            /** @var School $school */
            $school = $provisionedSchool['school'];

            /** @var User $admin */
            $admin = $provisionedSchool['admin'];

            $this->editingSchoolId = $school->id;
            $this->schoolAdminId = $admin->id;
            $this->createdSchoolCredentials = [
                'school_name' => $school->name,
                'school_slug' => $school->slug,
                'school_url' => $provisionedSchool['portal_url'],
                'admin_email' => $admin->email,
                'admin_password' => $provisionedSchool['plain_password'],
            ];
        } else {
            DB::transaction(function () use ($validated): void {
                $school = School::query()->findOrFail($this->editingSchoolId);
                $admin = $this->schoolAdminId
                    ? User::query()->findOrFail($this->schoolAdminId)
                    : $this->resolveSchoolAdmin($school);

                $school->update([
                    'name' => $validated['name'],
                    'slug' => $validated['slug'],
                    'email' => $validated['email'] ?: null,
                    'phone' => $validated['phone'] ?: null,
                    'address' => $validated['address'] ?: null,
                    'status' => $validated['status'],
                    'plan' => $validated['plan'],
                    'timezone' => $validated['timezone'],
                ]);

                if (! $admin) {
                    $admin = new User;
                    $admin->school_id = $school->id;
                }

                $admin->fill([
                    'school_id' => $school->id,
                    'name' => $validated['adminName'],
                    'email' => $validated['adminEmail'],
                    'status' => 'active',
                ]);

                if ($validated['adminPassword'] !== '') {
                    $admin->password = $validated['adminPassword'];
                }

                $admin->save();

                Role::findOrCreate('school_admin', 'web');
                $admin->syncRoles(['school_admin']);

                $this->editingSchoolId = $school->id;
                $this->schoolAdminId = $admin->id;
            });
        }

        session()->flash(
            'status',
            $isEditing ? 'School details updated successfully.' : 'School and school admin created successfully.',
        );

        $this->closeSchoolModal();
    }

    public function toggleSchoolSuspension(int $schoolId): void
    {
        $school = School::query()->findOrFail($schoolId);

        $nextStatus = $school->status === 'suspended' ? 'active' : 'suspended';

        $school->update([
            'status' => $nextStatus,
        ]);

        session()->flash(
            'status',
            $nextStatus === 'suspended'
                ? "{$school->name} has been suspended."
                : "{$school->name} has been reactivated.",
        );
    }

    public function confirmSchoolDeletion(int $schoolId): void
    {
        $this->deletingSchoolId = $schoolId;
        $this->showDeleteModal = true;
    }

    public function deleteSchool(): void
    {
        $school = School::query()->findOrFail($this->deletingSchoolId);
        $schoolUserIds = User::query()
            ->where('school_id', $school->id)
            ->pluck('id')
            ->all();

        DB::transaction(function () use ($school, $schoolUserIds): void {
            $school->delete();

            if ($schoolUserIds !== []) {
                User::query()
                    ->whereIn('id', $schoolUserIds)
                    ->delete();
            }
        });

        $schoolName = $school->name;
        $this->showDeleteModal = false;
        $this->deletingSchoolId = null;

        session()->flash('status', "{$schoolName} was deleted successfully.");
    }

    public function resetSchoolAdminPassword(int $schoolId): void
    {
        $school = School::query()->findOrFail($schoolId);
        $admin = $this->resolveSchoolAdmin($school);

        abort_unless($admin !== null, 404, 'School admin account not found.');

        $password = Str::password(12, true, true, false, false);

        $admin->password = $password;
        $admin->save();

        $this->temporaryPassword = $password;
        $this->temporaryPasswordSchoolName = $school->name;

        session()->flash('status', "School admin password reset for {$school->name}.");
    }

    public function editPaymentSettings(int $schoolId): void
    {
        if (! $this->hasPaymentSettingsTable()) {
            session()->flash('status', 'Run the payment settings migration before configuring school gateways.');

            return;
        }

        $school = School::query()
            ->with('paymentSetting')
            ->findOrFail($schoolId, ['id', 'name']);

        $setting = $school->paymentSetting;

        $this->resetValidation();
        $this->paymentSchoolId = $school->id;
        $this->paymentGatewayName = $setting?->gateway_name ?? 'paystack';
        $this->paystackPublicKey = $setting?->paystack_public_key ?? '';
        $this->paystackSecretKey = '';
        $this->paystackMode = $setting?->paystack_mode ?? 'test';
        $this->paymentSettingsActive = (bool) ($setting?->is_active ?? false);
        $this->merchantName = $setting?->merchant_name ?? '';
        $this->merchantEmail = $setting?->merchant_email ?? '';
        $this->merchantPhone = $setting?->merchant_phone ?? '';
        $this->hasStoredSecretKey = filled($setting?->getRawOriginal('paystack_secret_key'));
        $this->showPaymentSettingsModal = true;
    }

    public function savePaymentSettings(): void
    {
        if (! $this->hasPaymentSettingsTable()) {
            session()->flash('status', 'Run the payment settings migration before saving payment configuration.');

            return;
        }

        $school = School::query()->findOrFail($this->paymentSchoolId);
        $this->normalizePaymentSettingsFields();
        $validated = $this->validate($this->paymentSettingsRules());

        $setting = SchoolPaymentSetting::query()->firstOrNew([
            'school_id' => $school->id,
            'gateway_name' => $validated['paymentGatewayName'],
        ]);

        $setting->fill([
            'gateway_name' => $validated['paymentGatewayName'],
            'paystack_public_key' => $validated['paystackPublicKey'],
            'paystack_mode' => $validated['paystackMode'],
            'is_active' => $validated['paymentSettingsActive'],
            'merchant_name' => $validated['merchantName'] !== '' ? $validated['merchantName'] : null,
            'merchant_email' => $validated['merchantEmail'] !== '' ? $validated['merchantEmail'] : null,
            'merchant_phone' => $validated['merchantPhone'] !== '' ? $validated['merchantPhone'] : null,
        ]);

        if ($validated['paystackSecretKey'] !== '') {
            $setting->paystack_secret_key = $validated['paystackSecretKey'];
        }

        $setting->school()->associate($school);
        $setting->save();

        session()->flash('status', "Payment settings updated for {$school->name}.");

        $this->closePaymentSettingsModal();
    }

    public function closeSchoolModal(): void
    {
        $this->showSchoolModal = false;
        $this->create = '0';
        $this->resetForm();
    }

    public function closePaymentSettingsModal(): void
    {
        $this->showPaymentSettingsModal = false;
        $this->resetValidation();
        $this->resetPaymentSettingsForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingSchoolId = null;
    }

    public function render(): View
    {
        $paymentSettingsAvailable = $this->hasPaymentSettingsTable();
        $planColumnAvailable = $this->hasSchoolPlanColumn();
        $paymentMetrics = $this->paymentMetrics();
        $schoolMetrics = $this->schoolMetrics();
        $schools = $this->schoolsQuery()->paginate(10);

        if (! $paymentSettingsAvailable) {
            $schools->getCollection()->each(function (School $school): void {
                $school->setRelation('paymentSetting', null);
            });
        }

        $schools->getCollection()->each(function (School $school): void {
            $school->setAttribute('plan_usage', $this->planUsageForSchool($school));
        });

        return view('livewire.admin.schools.index', [
            'schools' => $schools,
            'planOptions' => self::PLAN_OPTIONS,
            'metrics' => [
                'total' => $schoolMetrics['total'],
                'active' => $schoolMetrics['active'],
                'suspended' => $schoolMetrics['suspended'],
                'inactive' => $schoolMetrics['inactive'],
                'payment_configured' => $paymentMetrics['configured'],
                'payment_active' => $paymentMetrics['active'],
                'payment_live' => $paymentMetrics['live'],
                'payment_test' => $paymentMetrics['test'],
            ],
            'paymentSettingsAvailable' => $paymentSettingsAvailable,
            'planColumnAvailable' => $planColumnAvailable,
            'paymentSchool' => $this->paymentSchoolId
                ? School::query()->find($this->paymentSchoolId, ['id', 'name', 'slug'])
                : null,
            'deletingSchool' => $this->deletingSchoolId
                ? School::query()->find($this->deletingSchoolId, ['id', 'name'])
                : null,
        ]);
    }

    protected function rules(): array
    {
        $slugRules = ['required', 'string', 'max:255', 'alpha_dash'];

        if ($this->editingSchoolId !== null) {
            $slugRules[] = Rule::unique('schools', 'slug')->ignore($this->editingSchoolId);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => $slugRules,
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'plan' => ['required', Rule::in(self::PLAN_OPTIONS)],
            'timezone' => ['required', 'timezone'],
            'adminName' => ['required', 'string', 'max:255'],
            'adminEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->schoolAdminId)],
            'adminPassword' => [
                Rule::requiredIf($this->editingSchoolId === null || $this->schoolAdminId === null),
                'nullable',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ];
    }

    protected function resetForm(): void
    {
        $this->editingSchoolId = null;
        $this->schoolAdminId = null;
        $this->name = '';
        $this->slug = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->status = 'active';
        $this->plan = 'free';
        $this->timezone = 'Africa/Lagos';
        $this->adminName = '';
        $this->adminEmail = '';
        $this->adminPassword = '';
        $this->adminPassword_confirmation = '';
    }

    protected function paymentSettingsRules(): array
    {
        return [
            'paymentGatewayName' => ['required', Rule::in(['paystack'])],
            'paystackPublicKey' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value)) {
                        return;
                    }

                    $expectedPrefix = $this->paystackMode === 'live' ? 'pk_live_' : 'pk_test_';

                    if (! str_starts_with($value, $expectedPrefix)) {
                        $fail("The Paystack public key must start with {$expectedPrefix} for {$this->paystackMode} mode.");
                    }
                },
            ],
            'paystackSecretKey' => [
                Rule::requiredIf(! $this->hasStoredSecretKey),
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if (! is_string($value)) {
                        return;
                    }

                    $expectedPrefix = $this->paystackMode === 'live' ? 'sk_live_' : 'sk_test_';

                    if (! str_starts_with($value, $expectedPrefix)) {
                        $fail("The Paystack secret key must start with {$expectedPrefix} for {$this->paystackMode} mode.");
                    }
                },
            ],
            'paystackMode' => ['required', Rule::in(['test', 'live'])],
            'paymentSettingsActive' => ['required', 'boolean'],
            'merchantName' => ['nullable', 'string', 'max:255'],
            'merchantEmail' => ['nullable', 'email', 'max:255'],
            'merchantPhone' => ['nullable', 'string', 'max:30'],
        ];
    }

    protected function resetPaymentSettingsForm(): void
    {
        $this->paymentSchoolId = null;
        $this->paymentGatewayName = 'paystack';
        $this->paystackPublicKey = '';
        $this->paystackSecretKey = '';
        $this->paystackMode = 'test';
        $this->paymentSettingsActive = false;
        $this->merchantName = '';
        $this->merchantEmail = '';
        $this->merchantPhone = '';
        $this->hasStoredSecretKey = false;
    }

    protected function normalizePaymentSettingsFields(): void
    {
        $this->paymentGatewayName = strtolower(trim($this->paymentGatewayName));
        $this->paystackPublicKey = trim($this->paystackPublicKey);
        $this->paystackSecretKey = trim($this->paystackSecretKey);
        $this->paystackMode = strtolower(trim($this->paystackMode));
        $this->merchantName = trim($this->merchantName);
        $this->merchantEmail = strtolower(trim($this->merchantEmail));
        $this->merchantPhone = trim($this->merchantPhone);
    }

    protected function normalizedSlug(): string
    {
        $slugSource = $this->slug !== '' ? $this->slug : $this->name;
        $normalizedSlug = Str::slug($slugSource);

        return $normalizedSlug !== '' ? $normalizedSlug : 'school';
    }

    protected function normalizedStoredPlan(mixed $plan): string
    {
        $normalizedPlan = strtolower(trim((string) $plan));

        return in_array($normalizedPlan, self::PLAN_OPTIONS, true) ? $normalizedPlan : 'free';
    }

    protected function normalizedSubmittedPlan(mixed $plan): string
    {
        return strtolower(trim((string) $plan));
    }

    protected function clearTemporaryPasswordNotice(): void
    {
        $this->temporaryPassword = null;
        $this->temporaryPasswordSchoolName = null;
    }

    protected function openCreateModal(): void
    {
        $this->createdSchoolCredentials = null;
        $this->clearTemporaryPasswordNotice();
        $this->resetValidation();
        $this->resetForm();
        $this->showSchoolModal = true;
    }

    protected function schoolsQuery(): Builder
    {
        $query = School::query()
            ->withCount([
                'schoolClasses',
                'students',
                'users as teachers_count' => fn (Builder $query) => $query
                    ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'teacher'))
                    ->whereDoesntHave('roles', fn ($roleQuery) => $roleQuery->whereIn('name', ['school_admin', 'super_admin'])),
                'users as parents_count' => fn (Builder $query) => $query
                    ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'parent'))
                    ->whereDoesntHave('roles', fn ($roleQuery) => $roleQuery->whereIn('name', ['school_admin', 'super_admin'])),
            ])
            ->with([
                'users' => fn ($query) => $query
                    ->select('users.id', 'users.school_id', 'users.name', 'users.email', 'users.status')
                    ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'school_admin'))
                    ->orderBy('users.id'),
            ])
            ->when(
                $this->statusFilter !== 'all',
                fn (Builder $query) => $query->where('status', $this->statusFilter),
            )
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('users', function (Builder $userQuery) use ($search): void {
                            $userQuery
                                ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'school_admin'))
                                ->where(function (Builder $adminQuery) use ($search): void {
                                    $adminQuery
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->latest();

        if ($this->hasPaymentSettingsTable()) {
            $query->with([
                'paymentSetting:id,school_id,gateway_name,paystack_mode,is_active,paystack_public_key,updated_at',
            ]);
        }

        return $query;
    }

    /**
     * @return array{
     *     plan:string,
     *     teachers: array{current:int, limit:int|null, label:string, near_limit:bool, at_limit:bool},
     *     students: array{current:int, limit:int|null, label:string, near_limit:bool, at_limit:bool},
     *     parents: array{current:int, limit:int|null, label:string, near_limit:bool, at_limit:bool},
     *     has_limit_reached: bool,
     *     has_warning: bool
     * }
     */
    protected function planUsageForSchool(School $school): array
    {
        $limits = app(SchoolPlanLimitService::class)->getPlanLimitsForSchool($school);
        $plan = $limits['plan'];
        $teachersUsage = $this->resourceUsage((int) ($school->teachers_count ?? 0), $limits['teachers']);
        $studentsUsage = $this->resourceUsage((int) ($school->students_count ?? 0), $limits['students']);
        $parentsUsage = $this->resourceUsage((int) ($school->parents_count ?? 0), $limits['parents']);

        return [
            'plan' => $plan,
            'teachers' => $teachersUsage,
            'students' => $studentsUsage,
            'parents' => $parentsUsage,
            'has_limit_reached' => $teachersUsage['at_limit'] || $studentsUsage['at_limit'] || $parentsUsage['at_limit'],
            'has_warning' => $teachersUsage['near_limit'] || $studentsUsage['near_limit'] || $parentsUsage['near_limit'],
        ];
    }

    /**
     * @return array{current:int, limit:int|null, label:string, near_limit:bool, at_limit:bool}
     */
    protected function resourceUsage(int $current, ?int $limit): array
    {
        if ($limit === null) {
            return [
                'current' => $current,
                'limit' => null,
                'label' => 'Unlimited',
                'near_limit' => false,
                'at_limit' => false,
            ];
        }

        $atLimit = $current >= $limit;
        $nearLimit = ! $atLimit && $current >= (int) ceil($limit * 0.8);

        return [
            'current' => $current,
            'limit' => $limit,
            'label' => number_format($current).' / '.number_format($limit),
            'near_limit' => $nearLimit,
            'at_limit' => $atLimit,
        ];
    }

    protected function resolveSchoolAdmin(School $school): ?User
    {
        return User::query()
            ->where('school_id', $school->id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'school_admin'))
            ->orderBy('id')
            ->first();
    }

    /**
     * @return array{configured: int, active: int, live: int, test: int}
     */
    protected function paymentMetrics(): array
    {
        if (! $this->hasPaymentSettingsTable()) {
            return [
                'configured' => 0,
                'active' => 0,
                'live' => 0,
                'test' => 0,
            ];
        }

        /** @var object{configured:int|string|null,active:int|string|null,live:int|string|null,test:int|string|null}|null $metrics */
        $metrics = Cache::remember(
            'admin.schools.payment-metrics',
            now()->addMinutes(2),
            fn () => SchoolPaymentSetting::query()
                ->selectRaw('COUNT(DISTINCT school_id) as configured')
                ->selectRaw('COUNT(DISTINCT CASE WHEN is_active = 1 THEN school_id END) as active')
                ->selectRaw("COUNT(DISTINCT CASE WHEN is_active = 1 AND paystack_mode = 'live' THEN school_id END) as live")
                ->selectRaw("COUNT(DISTINCT CASE WHEN is_active = 1 AND paystack_mode = 'test' THEN school_id END) as test")
                ->first()
        );

        return [
            'configured' => (int) ($metrics?->configured ?? 0),
            'active' => (int) ($metrics?->active ?? 0),
            'live' => (int) ($metrics?->live ?? 0),
            'test' => (int) ($metrics?->test ?? 0),
        ];
    }

    protected function hasPaymentSettingsTable(): bool
    {
        if ($this->paymentSettingsTableAvailable !== null) {
            return $this->paymentSettingsTableAvailable;
        }

        return $this->paymentSettingsTableAvailable = Schema::hasTable('school_payment_settings');
    }

    protected function hasSchoolPlanColumn(): bool
    {
        if ($this->schoolPlanColumnAvailable !== null) {
            return $this->schoolPlanColumnAvailable;
        }

        return $this->schoolPlanColumnAvailable = Schema::hasColumn('schools', 'plan');
    }

    /**
     * @return array{total: int, active: int, suspended: int, inactive: int}
     */
    protected function schoolMetrics(): array
    {
        /** @var object{total:int|string|null,active:int|string|null,suspended:int|string|null,inactive:int|string|null}|null $metrics */
        $metrics = Cache::remember(
            'admin.schools.metrics',
            now()->addMinutes(2),
            fn () => School::query()
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
                ->selectRaw("SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended")
                ->selectRaw("SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive")
                ->first()
        );

        return [
            'total' => (int) ($metrics?->total ?? 0),
            'active' => (int) ($metrics?->active ?? 0),
            'suspended' => (int) ($metrics?->suspended ?? 0),
            'inactive' => (int) ($metrics?->inactive ?? 0),
        ];
    }

    protected function shouldOpenCreateModal(): bool
    {
        return in_array($this->create, ['1', 'true', 'yes'], true);
    }
}
