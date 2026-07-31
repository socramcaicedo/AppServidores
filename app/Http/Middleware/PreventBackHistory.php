<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // No aplicar headers de cache a peticiones que esperan JSON (AJAX)
        if ($request->expectsJson()) {
            return $response;
        }

        // Prevenir caché del navegador para evitar navegación hacia atrás
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        // Headers de seguridad existentes
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');

        // Nuevos headers de seguridad adicionales
        // Content-Security-Policy: Previene XSS y ataques de inyección
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'none';");

        // Strict-Transport-Security: Fuerza HTTPS (solo si tienes HTTPS)
        // $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // Permissions-Policy: Controla qué características del navegador pueden usar
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        // Referrer-Policy: Controla información enviada en Referer header
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // X-XSS-Protection: Protección adicional contra XSS (para navegadores antiguos)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
