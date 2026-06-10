<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
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
