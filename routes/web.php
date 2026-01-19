<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes (login page)
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('auth.attempt');
});

// Logout (authenticated users only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes - require authentication
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Resource Routes
    Route::resource('patients', PatientController::class);
    Route::post('patients/{patient}/files', [PatientController::class, 'uploadFile'])->name('patients.upload-file');
    Route::get('patients/{patient}/files/{file}', [PatientController::class, 'downloadFile'])->name('patients.download-file');
    Route::delete('patients/{patient}/files/{file}', [PatientController::class, 'deleteFile'])->name('patients.delete-file');
    Route::get('doctors/{doctor}/schedule', [App\Http\Controllers\ScheduleController::class, 'show'])->name('doctors.schedule');
    Route::put('doctors/{doctor}/schedule', [App\Http\Controllers\ScheduleController::class, 'update'])->name('doctors.schedule.update');
    Route::post('doctors/{doctor}/leaves', [App\Http\Controllers\ScheduleController::class, 'storeLeave'])->name('doctors.leaves.store');
    Route::delete('doctors/{doctor}/leaves/{leave}', [App\Http\Controllers\ScheduleController::class, 'destroyLeave'])->name('doctors.leaves.destroy');
    Route::get('doctors/{doctor}/available-slots/{date}', [App\Http\Controllers\ScheduleController::class, 'getAvailableSlots'])->name('doctors.available-slots');
    Route::resource('doctors', DoctorController::class);
    Route::get('appointments/calendar', [AppointmentController::class, 'calendar'])->name('appointments.calendar');
    Route::get('appointments/events', [AppointmentController::class, 'events'])->name('appointments.events');
    Route::resource('appointments', AppointmentController::class);
    Route::post('appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn'])->name('appointments.check-in');
    Route::post('appointments/{appointment}/start', [AppointmentController::class, 'startVisit'])->name('appointments.start');
    Route::post('appointments/{appointment}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('appointments/{appointment}/no-show', [AppointmentController::class, 'markNoShow'])->name('appointments.no-show');

    // Settings
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');

    // User Management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });
});
