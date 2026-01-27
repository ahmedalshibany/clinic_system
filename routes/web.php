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
    Route::get('appointments/queue', [AppointmentController::class, 'queue'])->name('appointments.queue');
    Route::resource('appointments', AppointmentController::class);
    Route::post('appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn'])->name('appointments.check-in');
    Route::post('appointments/{appointment}/start', [AppointmentController::class, 'startVisit'])->name('appointments.start');
    Route::post('appointments/{appointment}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('appointments/{appointment}/no-show', [AppointmentController::class, 'markNoShow'])->name('appointments.no-show');
    // Medical Records & Prescriptions
    Route::get('medicalrecords-/create/{patient}', [App\Http\Controllers\MedicalRecordController::class, 'create'])->name('medical-records.create');
    Route::get('medical-records/{medical_record}/print-prescription', [App\Http\Controllers\MedicalRecordController::class, 'printPrescription'])->name('medical-records.print-prescription');
    Route::get('medical-records/{medical_record}/print-report', [App\Http\Controllers\MedicalRecordController::class, 'printReport'])->name('medical-records.print-report');
    Route::resource('medical-records', App\Http\Controllers\MedicalRecordController::class);

    Route::resource('services', App\Http\Controllers\ServiceController::class);

    // Invoices
    Route::get('invoices/{appointment}/create', [App\Http\Controllers\InvoiceController::class, 'createFromAppointment'])->name('invoices.create-from-appointment');
    Route::post('invoices/{invoice}/payment', [App\Http\Controllers\InvoiceController::class, 'recordPayment'])->name('invoices.payment');
    Route::post('invoices/{invoice}/send', [App\Http\Controllers\InvoiceController::class, 'send'])->name('invoices.send');
    Route::get('invoices/{invoice}/print', [App\Http\Controllers\InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('invoices/{invoice}/pdf', [App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::resource('invoices', App\Http\Controllers\InvoiceController::class);

    // Reports
    Route::middleware(['role:admin,doctor'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/revenue', [App\Http\Controllers\ReportController::class, 'revenue'])->name('revenue');
        Route::get('/revenue/doctor', [App\Http\Controllers\ReportController::class, 'revenueByDoctor'])->name('revenue.doctor');
        Route::get('/revenue/service', [App\Http\Controllers\ReportController::class, 'revenueByService'])->name('revenue.service');
        Route::get('/patients', [App\Http\Controllers\ReportController::class, 'patients'])->name('patients');
        Route::get('/appointments', [App\Http\Controllers\ReportController::class, 'appointments'])->name('appointments');
        Route::get('/outstanding', [App\Http\Controllers\ReportController::class, 'outstanding'])->name('outstanding');
        Route::get('/export/excel/{report}', [App\Http\Controllers\ReportController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf/{report}', [App\Http\Controllers\ReportController::class, 'exportPdf'])->name('export.pdf');
    });

    // Settings
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/latest', [\App\Http\Controllers\NotificationController::class, 'getLatest'])->name('notifications.latest');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::patch('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::delete('/notifications/clear-all', [\App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');

    // User Management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });

    // Nurse / Vitals Routes
    Route::middleware(['role:nurse,doctor,admin'])->group(function () {
        Route::get('appointments/{appointment}/vitals/create', [App\Http\Controllers\NurseController::class, 'createVitals'])->name('nurse.vitals.create');
        Route::post('appointments/{appointment}/vitals', [App\Http\Controllers\NurseController::class, 'storeVitals'])->name('nurse.vitals.store');
    });

    // API Internal Routes (AJAX)
    Route::get('/api/patients/search', [App\Http\Controllers\Api\PatientApiController::class, 'search'])->name('api.patients.search');
    Route::get('/api/medicines/search', [App\Http\Controllers\Api\MedicineApiController::class, 'search'])->name('api.medicines.search');
});
