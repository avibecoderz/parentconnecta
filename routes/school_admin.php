<?php

use App\Http\Controllers\School\AdminNotificationController;
use App\Livewire\School\Admin\Assignments\Index as AssignmentsIndex;
use App\Livewire\School\Admin\Classes\Index as ClassesIndex;
use App\Livewire\School\Admin\Dashboard;
use App\Livewire\School\Admin\Notifications\Index as NotificationsIndex;
use App\Livewire\School\Admin\Parents\Index as ParentsIndex;
use App\Livewire\School\Admin\Payments\Index as PaymentsIndex;
use App\Livewire\School\Admin\Students\Index as StudentsIndex;
use App\Livewire\School\Admin\Teachers\Index as TeachersIndex;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['role:school_admin'])
    ->group(function (): void {
        Route::get('dashboard', Dashboard::class)->name('dashboard');
        Route::get('notifications', NotificationsIndex::class)->name('notifications.index');
        Route::post('notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('notifications/{notification}/read', [AdminNotificationController::class, 'markAsRead'])
            ->whereUuid('notification')
            ->name('notifications.read');
        Route::get('teachers', TeachersIndex::class)->name('teachers.index');
        Route::get('parents', ParentsIndex::class)->name('parents.index');
        Route::get('classes', ClassesIndex::class)->name('classes.index');
        Route::get('students', StudentsIndex::class)->name('students.index');
        Route::get('assignments', AssignmentsIndex::class)->name('assignments.index');
        Route::get('payments', PaymentsIndex::class)->name('payments.index');
    });
