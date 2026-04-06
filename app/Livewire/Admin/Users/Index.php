<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Users')]
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
        $users = User::query()
            ->with(['school:id,name,slug', 'roles:id,name'])
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
                        })
                        ->orWhereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate(12);

        /** @var object{total:int|string|null,active:int|string|null}|null $metrics */
        $metrics = User::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->first();

        return view('livewire.admin.users.index', [
            'users' => $users,
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
