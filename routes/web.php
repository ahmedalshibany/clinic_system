<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes (login page)
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/login', function () {
        return view('auth.login');
    })->name('auth.login');
});

// Auth routes
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes (views only - data comes from API)
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/patients', function () {
    return view('patients.index');
})->name('patients.index');

Route::get('/doctors', function () {
    return view('doctors.index');
})->name('doctors.index');

Route::get('/appointments', function () {
    return view('appointments.index');
})->name('appointments.index');

Route::get('/settings', function () {
    return view('settings.index');
})->name('settings.index');
