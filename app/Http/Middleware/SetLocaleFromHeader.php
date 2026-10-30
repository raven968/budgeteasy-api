<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromHeader
{
    /**
     * Use the best match between the Accept-Language header and the supported locales.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasHeader('Accept-Language')) {
            app()->setLocale($request->getPreferredLanguage(config('app.supported_locales')));
        }

        $response = $next($request);

        $response->headers->set('Content-Language', app()->getLocale());

        return $response;
    }
}
