<?php

namespace App\Services\Schools;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class ProvisionSchool
{
    /**
     * @var list<string>
     */
    private const PLAN_OPTIONS = ['free', 'basic', 'premium'];

    /**
     * @param array{
     *     name: string,
     *     slug: string,
     *     email: string|null,
     *     phone: string|null,
     *     address: string|null,
     *     plan: string,
     *     timezone: string,
     *     adminName: string,
     *     adminEmail: string,
     *     adminPassword: string
     * } $attributes
     * @return array{school: School, admin: User, plain_password: string, portal_url: string}
     *
     * @throws ValidationException
     */
    public function create(array $attributes): array
    {
        $plan = $this->resolvePlan($attributes['plan'] ?? null);

        if (User::query()->where('email', $attributes['adminEmail'])->exists()) {
            throw ValidationException::withMessages([
                'adminEmail' => 'The school admin email has already been taken.',
            ]);
        }

        return DB::transaction(function () use ($attributes, $plan): array {
            $school = School::query()->create([
                'name' => $attributes['name'],
                'slug' => $this->generateUniqueSlug($attributes['slug'] ?: $attributes['name']),
                'email' => $attributes['email'] ?: null,
                'phone' => $attributes['phone'] ?: null,
                'address' => $attributes['address'] ?: null,
                'status' => 'active',
                'plan' => $plan,
                'timezone' => $attributes['timezone'],
            ]);

            $admin = User::query()->create([
                'school_id' => $school->id,
                'name' => $attributes['adminName'],
                'email' => $attributes['adminEmail'],
                'password' => $attributes['adminPassword'],
                'status' => 'active',
            ]);

            Role::findOrCreate('school_admin', 'web');
            $admin->assignRole('school_admin');

            return [
                'school' => $school,
                'admin' => $admin,
                'plain_password' => $attributes['adminPassword'],
                'portal_url' => route('school.admin.dashboard', ['slug' => $school->slug]),
            ];
        });
    }

    protected function generateUniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'school';

        $slug = $baseSlug;
        $suffix = 2;

        while (School::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    protected function resolvePlan(mixed $plan): string
    {
        $normalizedPlan = strtolower(trim((string) $plan));

        if (in_array($normalizedPlan, self::PLAN_OPTIONS, true)) {
            return $normalizedPlan;
        }

        throw ValidationException::withMessages([
            'plan' => 'The selected plan is invalid.',
        ]);
    }
}
