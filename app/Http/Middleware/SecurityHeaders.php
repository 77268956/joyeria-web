<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attaches defensive HTTP response headers to every web response.
 *
 * The strict Content-Security-Policy and HSTS headers are only emitted in
 * production: the Vite dev server needs WebSocket/eval in local, and HSTS on
 * a plain-HTTP development host would poison the browser for real users.
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        if (app()->isProduction()) {
            if ($request->secure()) {
                $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }

            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        }

        return $response;
    }

    /**
     * Content-Security-Policy permitting the storefront's own fonts (Google
     * Fonts), the Google Maps embed and the WhatsApp deep link, while blocking
     * any third-party script or framing.
     */
    protected function contentSecurityPolicy(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "img-src 'self' data: https:",
            "font-src 'self' https://fonts.gstatic.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "script-src 'self' 'unsafe-inline'",
            "connect-src 'self'",
            "frame-src 'self' https://www.google.com https://maps.google.com",
        ]);
    }
}
