<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))



->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'rol'      => \App\Http\Middleware\VerificarRol::class,
            'prevent.back' => \App\Http\Middleware\PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Intercepta el error 419 (CSRF token expirado) para mostrar un mensaje
        // amigable en lugar de la página en blanco por defecto de Laravel.
        $exceptions->respond(function ($response, \Throwable $e, $request) {
            if ($e instanceof TokenMismatchException) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Tu sesión expiró por inactividad.',
                    ], 419);
                }

                return redirect()->route('login')->with(
                    'error',
                    'Tu sesión expiró por inactividad. Por favor, inicia sesión nuevamente.'
                );
            }

            return $response;
        });
    })
       ->booted(function ($app) {
        $app['auth']->provider('usuarios-provider', function ($app, array $config) {
            return new \App\Auth\UsuarioUserProvider(
                $app['hash'],
                $config['model']
            );
        });
    })


    ->create();


