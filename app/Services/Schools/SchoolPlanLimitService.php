<?php

namespace App\Services\Schools;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class SchoolPlanLimitService
{
    /**
     * @var list<string>
     */
    private const EXCLUDED_ACCOUNT_ROLES = ['school_admin', 'super_admin'];

    /**
     * @var array<string, array{teachers:int|null, students:int|null, parents:int|null}>
     */
    private const PLAN_LIMITS = [
        'free' => [
            'teachers' => 2,
            'students' => 20,
            'parents' => 5,
        ],
        'basic' => [
            'teachers' => 20,
            'students' => 100,
            'parents' => 20,
        ],
        'premium' => [
            'teachers' => null,
            'students' => null,
            'parents' => null,
        ],
    ];

    /**
     * Get normalized plan limits for a school.
     *
     * @return array{plan:string, teachers:int|null, students:int|null, parents:int|null}
     */
    public function getPlanLimitsForSchool(School $school): array
    {
        $plan = $this->normalizePlan($school->plan);
        $limits = self::PLAN_LIMITS[$plan];

        return [
            'plan' => $plan,
            'teachers' => $limits['teachers'],
            'students' => $limits['students'],
            'parents' => $limits['parents'],
        ];
    }

    public function canAddTeacher(School $school): bool
    {
        $limit = $this->getPlanLimitsForSchool($school)['teachers'];

        if ($this->isUnlimited($limit)) {
            return true;
        }

        return $this->teacherCount($school) < $limit;
    }

    public function canAddStudent(School $school): bool
    {
        $limit = $this->getPlanLimitsForSchool($school)['students'];

        if ($this->isUnlimited($limit)) {
            return true;
        }

        return $this->studentCount($school) < $limit;
    }

    public function canAddParent(School $school): bool
    {
        $limit = $this->getPlanLimitsForSchool($school)['parents'];

        if ($this->isUnlimited($limit)) {
            return true;
        }

        return $this->parentCount($school) < $limit;
    }

    public function ensureCanAddTeacher(School $school, string $errorKey = 'name'): void
    {
        $this->ensureCanAdd($school, 'teachers', 'Teacher', $errorKey);
    }

    public function ensureCanAddStudent(School $school, string $errorKey = 'firstName'): void
    {
        $this->ensureCanAdd($school, 'students', 'Student', $errorKey);
    }

    public function ensureCanAddParent(School $school, string $errorKey = 'name'): void
    {
        $this->ensureCanAdd($school, 'parents', 'Parent', $errorKey);
    }

    protected function teacherCount(School $school): int
    {
        return $this->roleUserCount($school, 'teacher');
    }

    protected function studentCount(School $school): int
    {
        return Student::query()
            ->where('school_id', $school->id)
            ->count();
    }

    protected function parentCount(School $school): int
    {
        return $this->roleUserCount($school, 'parent');
    }

    protected function roleUserCount(School $school, string $roleName): int
    {
        return User::query()
            ->where('school_id', $school->id)
            ->whereHas('roles', fn (Builder $query) => $query->where('name', $roleName))
            ->whereDoesntHave('roles', fn (Builder $query) => $query->whereIn('name', self::EXCLUDED_ACCOUNT_ROLES))
            ->count();
    }

    protected function normalizePlan(mixed $plan): string
    {
        $normalizedPlan = strtolower(trim((string) $plan));

        return array_key_exists($normalizedPlan, self::PLAN_LIMITS) ? $normalizedPlan : 'free';
    }

    protected function isUnlimited(?int $limit): bool
    {
        return $limit === null;
    }

    protected function ensureCanAdd(School $school, string $resourceKey, string $resourceLabel, string $errorKey): void
    {
        /** @var School|null $lockedSchool */
        $lockedSchool = School::query()
            ->whereKey($school->id)
            ->lockForUpdate()
            ->first();

        if ($lockedSchool instanceof School) {
            $school = $lockedSchool;
        } else {
            $school->refresh();
        }

        $limits = $this->getPlanLimitsForSchool($school);
        $planLabel = ucfirst($limits['plan']);
        $limit = $limits[$resourceKey] ?? null;

        $currentCount = match ($resourceKey) {
            'teachers' => $this->teacherCount($school),
            'students' => $this->studentCount($school),
            'parents' => $this->parentCount($school),
            default => 0,
        };

        if ($limit === null || $currentCount < $limit) {
            return;
        }

        throw ValidationException::withMessages([
            $errorKey => "{$resourceLabel} limit reached for {$planLabel} plan ({$limit} max). Upgrade the school plan to add more {$this->pluralLowerLabel($resourceLabel)}.",
        ]);
    }

    protected function pluralLowerLabel(string $label): string
    {
        return strtolower($label).'s';
    }
}
