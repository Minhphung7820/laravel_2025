<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->header('Accept-Language', 'vi');
        if (!in_array($lang, ['vi', 'en'])) {
            $lang = 'vi';
        }
        App::setLocale($lang);
        return $next($request);
    }
}
