<?php

use App\Livewire\School\Parent\Dashboard;
use App\Livewire\School\Parent\Payments\Index as ParentPaymentsIndex;
use App\Livewire\School\Parent\Payments\Records as ParentPaymentRecords;
use App\Livewire\School\Parent\Pupils\Index as ParentPupilsIndex;
use App\Livewire\School\Parent\Pupils\Show as ParentPupilShow;
use Illuminate\Support\Facades\Route;

Route::prefix('parent')
    ->name('parent.')
    ->middleware(['role:parent'])
    ->group(function (): void {
        Route::get('dashboard', Dashboard::class)->name('dashboard');
        Route::get('payments', ParentPaymentsIndex::class)->name('payments.index');
        Route::get('payments/outstanding-records', ParentPaymentRecords::class)->name('payments.outstanding-records');
        Route::get('payments/outstanding-balance', ParentPaymentRecords::class)->name('payments.outstanding-balance');
        Route::get('payments/paid-records', ParentPaymentRecords::class)->name('payments.paid-records');

        Route::prefix('pupils')
            ->name('pupils.')
            ->group(function (): void {
                Route::get('/', ParentPupilsIndex::class)->name('index');
                Route::get('{student}', ParentPupilShow::class)->name('show');
            });
    });
