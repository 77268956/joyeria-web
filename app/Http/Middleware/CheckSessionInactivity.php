<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionInactivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity_time');
            $timeoutSeconds = config('session.inactivity_timeout', 1800); // 30 minutes default

            if ($lastActivity && (time() - $lastActivity > $timeoutSeconds)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Tu sesión ha expirado por inactividad. Por favor, ingresa de nuevo por seguridad.',
                ]);
            }

            session(['last_activity_time' => time()]);
        }

        return $next($request);
    }
}
