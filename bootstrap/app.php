<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\ServiceException;
use App\Traits\ApiResponseTrait;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Apply API middleware to all API routes
        $middleware->group('api', [
            \App\Http\Middleware\ApiMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle ServiceException (our custom business logic exceptions)
        $exceptions->render(function (ServiceException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->getErrors(),
                ], $e->getCode() ?: 400);
            }
        });

        // Handle Validation exceptions
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Handle Authentication exceptions
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'errors' => null,
                ], 401);
            }
        });

        // Handle Authorization exceptions
        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'This action is unauthorized',
                    'errors' => null,
                ], 403);
            }
        });

        // Handle Model Not Found exceptions
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'errors' => null,
                ], 404);
            }
        });

        // Handle 404 Not Found exceptions
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested resource was not found',
                    'errors' => null,
                ], 404);
            }
        });

        // Handle Method Not Allowed exceptions
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed',
                    'errors' => null,
                ], 405);
            }
        });

        // Handle other HTTP exceptions
        $exceptions->render(function (HttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'An error occurred',
                    'errors' => null,
                ], $e->getStatusCode());
            }
        });

        // Handle database connection errors
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $message = config('app.debug') 
                    ? $e->getMessage() 
                    : 'Database connection error';
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => null,
                ], 500);
            }
        });

        // Handle general exceptions (fallback)
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $message = config('app.debug') 
                    ? $e->getMessage() 
                    : 'Internal server error';

                $errors = config('app.debug') ? [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null;

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $errors,
                ], 500);
            }
        });
    })->create();
