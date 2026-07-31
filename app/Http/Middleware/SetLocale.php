<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        abort_unless(
            is_string($locale) &&
            in_array($locale, ['en', 'es'], true),
            404
        );

        App::setLocale($locale);

        return $next($request);
    }
}