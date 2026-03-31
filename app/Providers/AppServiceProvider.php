<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // @rol('1') ... @endrol
        Blade::directive('rol', function ($rol) {
            return "<?php if(auth()->check() && auth()->user()->rol_id == $rol): ?>";
        });

       Blade::directive('rol', function ($rol) {
    return "<?php if(auth()->check() && auth()->user()->tieneRol($rol)): ?>";
});
    }
}