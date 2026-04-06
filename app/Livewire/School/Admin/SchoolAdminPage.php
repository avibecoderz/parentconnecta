<?php

namespace App\Livewire\School\Admin;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

abstract class SchoolAdminPage extends Component
{
    use AuthorizesRequests;

    protected const SCHOOL_CONTEXT_COLUMNS = [
        'id',
        'name',
        'slug',
        'status',
        'plan',
        'timezone',
        'current_academic_year',
        'current_term',
    ];

    public string $slug;

    protected ?School $resolvedSchool = null;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    protected function currentSchool(): School
    {
        if ($this->resolvedSchool instanceof School) {
            return $this->resolvedSchool;
        }

        $currentSchool = request()->attributes->get('currentSchool');

        if ($currentSchool instanceof School) {
            if (! array_key_exists('plan', $currentSchool->getAttributes())) {
                $currentSchool = School::query()
                    ->whereKey($currentSchool->id)
                    ->firstOrFail(self::SCHOOL_CONTEXT_COLUMNS);

                request()->attributes->set('currentSchool', $currentSchool);
                view()->share('currentSchool', $currentSchool);
            }

            return $this->resolvedSchool = $currentSchool;
        }

        return $this->resolvedSchool = School::query()
            ->where('slug', $this->slug)
            ->firstOrFail(self::SCHOOL_CONTEXT_COLUMNS);
    }

    protected function schoolRoute(string $routeName, array $parameters = []): string
    {
        return route($routeName, ['slug' => $this->slug, ...$parameters]);
    }

    protected function usersByRoleQuery(School $school, string $role): Builder
    {
        return User::query()
            ->where('users.school_id', $school->id)
            ->whereHas('roles', fn (Builder $query) => $query->where('name', $role));
    }
}
