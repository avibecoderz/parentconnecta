<?php

namespace App\Livewire\Admin\SchoolAdmins;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('School Admins')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    protected string $paginationTheme = 'tailwind';

    public function updatedSearch(string $value): void
    {
        $this->search = $this->normalizedSearch($value);
        $this->resetPage();
    }

    public function render(): View
    {
        $schoolAdminRoleId = DB::table('roles')
            ->where('name', 'school_admin')
            ->where('guard_name', 'web')
            ->value('id');

        $schoolAdmins = User::query()
            ->with(['school:id,name,slug,status'])
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'school_admin'))
            ->when($this->search !== '', function (Builder $query): void {
                $search = $this->normalizedSearch($this->search);

                $query->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('school', function (Builder $schoolQuery) use ($search): void {
                            $schoolQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('name')
            ->paginate(10);

        $metrics = $schoolAdminRoleId !== null
            ? DB::table('model_has_roles')
                ->join('users', 'users.id', '=', 'model_has_roles.model_id')
                ->where('model_has_roles.role_id', $schoolAdminRoleId)
                ->where('model_has_roles.model_type', User::class)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN users.status = 'active' THEN 1 ELSE 0 END) as active")
                ->first()
            : null;

        return view('livewire.admin.school-admins.index', [
            'schoolAdmins' => $schoolAdmins,
            'metrics' => [
                'total' => (int) ($metrics?->total ?? 0),
                'active' => (int) ($metrics?->active ?? 0),
            ],
        ]);
    }

    protected function normalizedSearch(string $value): string
    {
        return trim($value);
    }
}
