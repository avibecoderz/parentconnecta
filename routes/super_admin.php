<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Notifications\Index as NotificationsIndex;
use App\Livewire\Admin\SchoolAdmins\Index as SchoolAdminsIndex;
use App\Livewire\Admin\Schools\Index as SchoolsIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('super-admin.')
    ->middleware(['auth', 'role:super_admin'])
    ->group(function (): void {
        Route::get('dashboard', Dashboard::class)->name('dashboard');
        Route::get('schools', SchoolsIndex::class)->name('schools.index');
        Route::get('notifications', NotificationsIndex::class)->name('notifications.index');
        Route::get('school-admins', SchoolAdminsIndex::class)->name('school-admins.index');
        Route::get('users', UsersIndex::class)->name('users.index');
    });
