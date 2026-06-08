<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Priority 1: URL ?lang= parameter
        if ($request->has('lang') && in_array($request->lang, ['en', 'ar'])) {
            session(['locale' => $request->lang]);
            app()->setLocale($request->lang);
            return $next($request);
        }

        // Priority 2: Session
        if (session()->has('locale')) {
            $locale = session('locale');
        }
        // Priority 3: Cookie (set by JS as a fallback across sessions)
        elseif ($request->cookie('clinic_lang')) {
            $locale = $request->cookie('clinic_lang');
            session(['locale' => $locale]);
        }
        // Priority 4: Config default
        else {
            $locale = config('app.locale', 'en');
        }

        if (!in_array($locale, ['en', 'ar'])) {
            $locale = config('app.locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
