<?php

namespace App\Models\Concerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait EnforcesSchoolTenancy
{
    protected static function bootEnforcesSchoolTenancy(): void
    {
        static::addGlobalScope('school_tenant', function (Builder $builder): void {
            $user = auth()->user();

            if ($user === null || $user->isSuperAdmin()) {
                return;
            }

            if ($user->school_id === null) {
                $builder->whereRaw('1 = 0');

                return;
            }

            $builder->where(
                $builder->getModel()->qualifyColumn('school_id'),
                $user->school_id,
            );
        });

        static::creating(function (Model $model): void {
            $tenantSchoolId = static::tenantSchoolIdFor($model);

            if ($tenantSchoolId !== null) {
                $model->school_id = $tenantSchoolId;
            }
        });

        static::updating(function (Model $model): void {
            $tenantSchoolId = static::tenantSchoolIdFor($model);

            if ($tenantSchoolId === null) {
                return;
            }

            if ((int) $model->school_id !== $tenantSchoolId) {
                throw new AuthorizationException('You are not allowed to modify records outside your school.');
            }
        });
    }

    protected static function tenantSchoolIdFor(Model $model): ?int
    {
        $user = auth()->user();

        if ($user === null || $user->isSuperAdmin()) {
            return $model->school_id !== null ? (int) $model->school_id : null;
        }

        if ($user->school_id === null) {
            throw new AuthorizationException('Your account is not assigned to a school.');
        }

        if ($model->school_id !== null && (int) $model->school_id !== (int) $user->school_id) {
            throw new AuthorizationException('You are not allowed to modify records outside your school.');
        }

        return (int) $user->school_id;
    }
}
