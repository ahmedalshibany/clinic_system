<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All API routes are session-authenticated via the auth middleware group.
| These endpoints are consumed internally by AJAX calls from the Blade UI.
|
*/

// Authenticated API routes
Route::middleware('auth')->group(function () {
    // Internal AJAX search endpoints — locked to clinical front-desk roles
    Route::middleware('role:admin,doctor,receptionist,nurse')->group(function () {
        Route::get('/patients/search', [App\Http\Controllers\Api\PatientApiController::class, 'search']);
        Route::get('/medicines/search', [App\Http\Controllers\Api\MedicineApiController::class, 'search']);
    });

    // Dashboard API endpoints — restricted to admin & doctor (aggregate/financial data)
    Route::middleware(['auth', 'role:admin,doctor'])->group(function () {
        Route::get('/dashboard/stats', [App\Http\Controllers\Api\DashboardApiController::class, 'stats']);
        Route::get('/dashboard/weekly-trend', [App\Http\Controllers\Api\DashboardApiController::class, 'weeklyTrend']);
        Route::get('/dashboard/recent-appointments', [App\Http\Controllers\Api\DashboardApiController::class, 'recentAppointments']);
        Route::get('/dashboard/status-distribution', [App\Http\Controllers\Api\DashboardApiController::class, 'statusDistribution']);
    });
});
