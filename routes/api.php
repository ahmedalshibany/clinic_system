<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/user', [AuthController::class, 'user']);

// Dashboard routes
Route::prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/weekly-trend', [DashboardController::class, 'weeklyTrend']);
    Route::get('/recent-appointments', [DashboardController::class, 'recentAppointments']);
    Route::get('/status-distribution', [DashboardController::class, 'statusDistribution']);
});

// Patients routes
Route::prefix('patients')->group(function () {
    Route::get('/', [PatientController::class, 'index']);
    Route::post('/', [PatientController::class, 'store']);
    Route::get('/search', [PatientController::class, 'search']);
    Route::get('/{patient}', [PatientController::class, 'show']);
    Route::put('/{patient}', [PatientController::class, 'update']);
    Route::delete('/{patient}', [PatientController::class, 'destroy']);
    Route::get('/{patient}/history', [PatientController::class, 'history']);
});

// Doctors routes
Route::prefix('doctors')->group(function () {
    Route::get('/', [DoctorController::class, 'index']);
    Route::post('/', [DoctorController::class, 'store']);
    Route::get('/search', [DoctorController::class, 'search']);
    Route::get('/{doctor}', [DoctorController::class, 'show']);
    Route::put('/{doctor}', [DoctorController::class, 'update']);
    Route::delete('/{doctor}', [DoctorController::class, 'destroy']);
    Route::get('/{doctor}/schedule', [DoctorController::class, 'schedule']);
});

// Appointments routes
Route::prefix('appointments')->group(function () {
    Route::get('/', [AppointmentController::class, 'index']);
    Route::post('/', [AppointmentController::class, 'store']);
    Route::get('/today', [AppointmentController::class, 'today']);
    Route::get('/recent', [AppointmentController::class, 'recent']);
    Route::get('/stats', [AppointmentController::class, 'stats']);
    Route::get('/weekly-trend', [AppointmentController::class, 'weeklyTrend']);
    Route::get('/{appointment}', [AppointmentController::class, 'show']);
    Route::put('/{appointment}', [AppointmentController::class, 'update']);
    Route::delete('/{appointment}', [AppointmentController::class, 'destroy']);
});
