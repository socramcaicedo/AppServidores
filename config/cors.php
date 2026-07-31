<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar las opciones de CORS para tu aplicación.
    | Esto determina qué dominios externos pueden acceder a tu API.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        // Solo permitir orígenes específicos (no usar '*' en producción)
        'http://localhost:3000',
        'http://127.0.0.1:8000',
        'http://192.168.18.253', // Tu IP local actual
        // Agrega aquí tu dominio de producción cuando lo tengas:
        // 'https://tu-dominio.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'X-CSRF-TOKEN',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];