<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('locale');

        if (!$locale) {
            $locale = $request->cookie('clinic_lang');
        }

        if (!$locale) {
            $locale = 'ar';
        }

        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'ar';
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }
}
