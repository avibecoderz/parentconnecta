<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (request()->user() !== null) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('dashboard', function () {
    $user = request()->user();

    if ($user?->hasRole('super_admin')) {
        return redirect()->route('super-admin.dashboard');
    }

    if ($user?->school?->slug) {
        if ($user->hasRole('school_admin')) {
            return redirect()->route('school.admin.dashboard', ['slug' => $user->school->slug]);
        }

        if ($user->hasRole('teacher')) {
            return redirect()->route('school.teacher.dashboard', ['slug' => $user->school->slug]);
        }

        if ($user->hasRole('parent')) {
            return redirect()->route('school.parent.dashboard', ['slug' => $user->school->slug]);
        }
    }

    return view('dashboard');
})
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');
