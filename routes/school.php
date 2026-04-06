<?php

use App\Models\School;
use Illuminate\Support\Facades\Route;

Route::prefix('school/{slug}')
    ->name('school.')
    ->middleware(['school.slug', 'school.active'])
    ->group(function (): void {
        Route::get('/', function (string $slug) {
            /** @var School|null $school */
            $school = request()->attributes->get('currentSchool');
            $user = request()->user();

            if (! $school instanceof School) {
                abort(404);
            }

            if ($user === null) {
                session(['url.intended' => route('school.entry', ['slug' => $slug])]);

                return redirect()->route('login');
            }

            if (! $user->isSuperAdmin() && ! $user->belongsToSchool($school)) {
                abort(403, 'You do not belong to this school.');
            }

            if ($user->isSuperAdmin() || $user->isSchoolAdmin()) {
                return redirect()->route('school.admin.dashboard', ['slug' => $slug]);
            }

            if ($user->isTeacher()) {
                return redirect()->route('school.teacher.dashboard', ['slug' => $slug]);
            }

            if ($user->isParent()) {
                return redirect()->route('school.parent.dashboard', ['slug' => $slug]);
            }

            abort(403, 'No school workspace is available for this account.');
        })->name('entry');

        Route::middleware(['auth', 'school.user'])->group(function (): void {
            require __DIR__.'/school_admin.php';
            require __DIR__.'/teacher.php';
            require __DIR__.'/parent.php';
        });
    });
