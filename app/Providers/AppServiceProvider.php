<?php

namespace App\Providers;

use App\Events\PaymentCreated;
use App\Listeners\RecalculateInvoiceStatus;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Setting;
use App\Observers\AppointmentObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(AuthServiceProvider::class);
    }

    public function boot(): void
    {
        if (!extension_loaded('bcmath')) {
            throw new \RuntimeException(
                'The BCMath PHP extension is required for financial calculations. ' .
                'Please install it: https://www.php.net/manual/en/book.bc.php'
            );
        }

        Appointment::observe(AppointmentObserver::class);

        Appointment::deleting(function (Appointment $appointment) {
            if ($appointment->isForceDeleting()) {
                return;
            }
            $appointment->vital()->delete();
            if ($appointment->medicalRecord) {
                $appointment->medicalRecord->delete();
            }
        });

        MedicalRecord::deleting(function (MedicalRecord $record) {
            if ($record->isForceDeleting()) {
                return;
            }
            if ($record->prescription) {
                $record->prescription->items()->delete();
                $record->prescription->delete();
            }
        });

        Paginator::useBootstrapFive();

        View::composer('*', function ($view) {
            try {
                $currencySymbol = Setting::get('currency_symbol', '﷼');
            } catch (\Exception $e) {
                $currencySymbol = '﷼';
            }
            $view->with('currencySymbol', $currencySymbol);
        });
    }
}
