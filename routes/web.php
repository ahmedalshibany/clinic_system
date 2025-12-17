<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Login page (guest only)
Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::get('/login', function () {
    return view('auth.login');
})->name('auth.login');

// For now, we'll create simple routes without auth middleware
// Later you can add authentication

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

// Logout route (placeholder for now)
Route::post('/logout', function () {
    return redirect('/');
})->name('logout');
