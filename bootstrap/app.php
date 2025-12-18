<?php

use App\Console\Commands\MakePlatformService;
use App\Http\Helpers\Responser;
use App\Http\Middleware\Localize;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            Route::group(['prefix' => 'api', 'middleware' => ['api', 'localize']], function () {
                Route::prefix('v1')->group(function () {
                    Route::prefix('admin')
                        ->group(base_path('routes/api/v1/admin.php'));

                    Route::prefix('merchant')
                        ->middleware([InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])
                        ->group(base_path('routes/api/v1/merchant.php'));
                });
            });
        },
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'localize' => Localize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return Responser::fail(status: $e->getStatusCode(), message: __('No data found'));
            }
        });
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof TokenExpiredException) {
                return Responser::fail(status: Response::HTTP_UNAUTHORIZED, message: 'Token expired');
            }

            if ($e instanceof TokenBlacklistedException) {
                return Responser::fail(status: Response::HTTP_UNAUTHORIZED, message: 'Token blacklisted');
            }

            if ($e instanceof TokenInvalidException) {
                return Responser::fail(status: Response::HTTP_UNAUTHORIZED, message: 'Token invalid');
            }

            if ($e instanceof JWTException) {
                return Responser::fail(status: Response::HTTP_UNAUTHORIZED, message: 'JWT error');
            }

            if ($e instanceof AuthenticationException) {
                return Responser::fail(status: Response::HTTP_UNAUTHORIZED, message: 'Unauthenticated');
            }

            if ($e instanceof ValidationException) {
                $errors = $e->validator->errors()->messages();

                return Responser::fail(message: 'Validation error', data: $errors);
            }

            Log::error('ERROR_CATCH:', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ]);
        });
    })->create();
