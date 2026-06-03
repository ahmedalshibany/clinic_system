<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Vital;
use App\Policies\AppointmentPolicy;
use App\Policies\DoctorPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\MedicalRecordPolicy;
use App\Policies\PatientPolicy;
use App\Policies\ServicePolicy;
use App\Policies\SettingPolicy;
use App\Policies\VitalPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Patient::class => PatientPolicy::class,
        Appointment::class => AppointmentPolicy::class,
        MedicalRecord::class => MedicalRecordPolicy::class,
        Doctor::class => DoctorPolicy::class,
        Invoice::class => InvoicePolicy::class,
        Service::class => ServicePolicy::class,
        Setting::class => SettingPolicy::class,
        Vital::class => VitalPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
