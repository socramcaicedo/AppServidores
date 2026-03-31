<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
   

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'rol'      => \App\Http\Middleware\VerificarRol::class,
        'permiso'  => \App\Http\Middleware\VerificarPermiso::class,
    ]);
})


->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
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


 
