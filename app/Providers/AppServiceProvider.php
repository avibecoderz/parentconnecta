<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Result;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Policies\PaymentPolicy;
use App\Policies\ResultPolicy;
use App\Policies\SchoolPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        $this->normalizePermissionModelTypes();

        Gate::policy(School::class, SchoolPolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Result::class, ResultPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);

        Gate::before(function (User $user): ?bool {
            return $user->isSuperAdmin() ? true : null;
        });

        Gate::define('manage-school-class', function (User $user, SchoolClass $schoolClass): bool {
            if ($user->isSchoolAdmin()) {
                return $user->belongsToSchool($schoolClass->school);
            }

            return $user->isAssignedToClass($schoolClass);
        });
    }

    protected function normalizePermissionModelTypes(): void
    {
        try {
            $normalizedModelType = User::class;
            $escapedModelType = str_replace('\\', '\\\\', $normalizedModelType);
            $updated = false;

            if (Schema::hasTable('model_has_roles')) {
                $updated = DB::table('model_has_roles')
                    ->where('model_type', $escapedModelType)
                    ->update(['model_type' => $normalizedModelType]) > 0 || $updated;
            }

            if (Schema::hasTable('model_has_permissions')) {
                $updated = DB::table('model_has_permissions')
                    ->where('model_type', $escapedModelType)
                    ->update(['model_type' => $normalizedModelType]) > 0 || $updated;
            }

            if ($updated) {
                app(PermissionRegistrar::class)->forgetCachedPermissions();
            }
        } catch (\Throwable) {
            // Ignore boot-time normalization errors when the database is unavailable.
        }
    }
}
