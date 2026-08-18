<?php

// Dynamically alias old App\Models namespace to the new App\Repository\Models namespace
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\Models\\')) {
        $target = str_replace('App\\Models\\', 'App\\Repository\\Models\\', $class);
        if (class_exists($target)) {
            class_alias($target, $class);
        }
    }
}, true, true);

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RequerirCambioContrasena;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            RequerirCambioContrasena::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
            'webhooks/stripe',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $exception, Request $request) {
            // En modo depuración local (APP_DEBUG=true), permitir que Laravel/Ignition muestre la traza detallada
            if (config('app.debug')) {
                return null;
            }

            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            if ($exception instanceof AuthenticationException
                || $exception instanceof ValidationException
                || $exception instanceof HttpResponseException
            ) {
                return null;
            }

            $status = 500;

            if ($exception instanceof HttpExceptionInterface) {
                $status = $exception->getStatusCode();
            } elseif ($exception instanceof ModelNotFoundException) {
                $status = 404;
            } elseif ($exception instanceof AuthorizationException) {
                $status = 403;
            } elseif ($exception instanceof TokenMismatchException) {
                $status = 419;
            }

            if ($request->header('X-Inertia') || $request->header('x-inertia')) {
                $inertiaResponse = Inertia::render('Error', [
                    'status' => $status,
                    'message' => $exception->getMessage() ?: null,
                ])->toResponse($request);

                $inertiaResponse->setStatusCode($status);
                $inertiaResponse->headers->set('X-Inertia', 'true');

                return $inertiaResponse;
            }

            $vista = view()->exists("errors.{$status}") ? "errors.{$status}" : 'errors.500';

            return response()->view($vista, ['codigo' => $status], $status);
        });
    })->create();
