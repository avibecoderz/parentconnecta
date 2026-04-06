<?php

use App\Livewire\School\Teacher\Assignments\Index as TeacherAssignmentsIndex;
use App\Livewire\School\Teacher\Classes\Index as TeacherClassesIndex;
use App\Livewire\School\Teacher\Dashboard;
use App\Livewire\School\Teacher\Payments\Index as TeacherPaymentsIndex;
use App\Livewire\School\Teacher\Results\Index as TeacherResultsIndex;
use App\Livewire\School\Teacher\Students\Index as TeacherStudentsIndex;
use Illuminate\Support\Facades\Route;

Route::prefix('teacher')
    ->name('teacher.')
    ->middleware(['role:teacher'])
    ->group(function (): void {
        Route::get('dashboard', Dashboard::class)->name('dashboard');
        Route::get('classes', TeacherClassesIndex::class)->name('classes.index');
        Route::get('students', TeacherStudentsIndex::class)->name('students.index');
        Route::get('assignments', TeacherAssignmentsIndex::class)->name('assignments.index');
        Route::get('results', TeacherResultsIndex::class)->name('results.index');
        Route::get('payments', TeacherPaymentsIndex::class)->name('payments.index');
    });
