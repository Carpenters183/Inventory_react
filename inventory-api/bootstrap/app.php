<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Cors; // Pastikan ini di-import

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', 
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // HAPUS pendaftaran dari $middleware->api(...)
        
        // *** TAMBAHKAN DI SINI: Daftarkan secara Global menggunakan append ***
        $middleware->append(
            Cors::class,
        );
        // ********************************************************************
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
    })
    ->create();