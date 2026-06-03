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
    // Internal AJAX search endpoints
    Route::get('/patients/search', [App\Http\Controllers\Api\PatientApiController::class, 'search']);
    Route::get('/medicines/search', [App\Http\Controllers\Api\MedicineApiController::class, 'search']);
});
