<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RolMiddleware;
use App\Http\Middleware\UsuarioActivo;
use App\Http\Middleware\InactividadSesion;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'inactividad' => InactividadSesion::class,
            'activo' => UsuarioActivo::class,
            'rol' => RolMiddleware::class,  
        ]);
         $middleware->appendToGroup('web', [
            InactividadSesion::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void { 
        //
    })->create();
// 