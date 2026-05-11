<?php

use App\Exceptions\ErrorInternoException;
use App\Exceptions\HotelException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (QueryException $e) {
            return response()->view('errors.500', [
                'exception' => new ErrorInternoException(
                    'Ha ocurrido un error en la base de datos. Por favor, verifique la información e intente de nuevo.'
                ),
            ], 500);
        });

        $exceptions->render(function (Throwable $e) {
            if ($e instanceof HotelException) {
                return response()->view("errors.{$e->getStatusCode()}", [
                    'exception' => $e,
                ], $e->getStatusCode());
            }

            if (! config('app.debug')) {
                return response()->view('errors.500', [
                    'exception' => new ErrorInternoException(
                        'Se ha producido un error inesperado en el servidor.'
                    ),
                ], 500);
            }
        });
    })->create();
