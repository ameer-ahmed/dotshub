<?php

use App\Http\Controllers\V1\System\Auth\AuthController;
use App\Http\Controllers\V1\System\Role\RoleController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::group(['middleware' => 'auth:user'], function () {
    Route::group(['prefix' => 'auth/sign', 'controller' => AuthController::class], function () {
        Route::post('up', 'signUp')
            ->withoutMiddleware('auth:user')
            ->withoutMiddleware(InitializeTenancyByDomain::class)
            ->withoutMiddleware(PreventAccessFromCentralDomains::class);
        Route::post('in', 'signIn')->withoutMiddleware('auth:user');
        Route::post('out', 'signOut');
    });

    Route::group(['prefix' => 'roles', 'controller' => RoleController::class], function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'delete');
    });
});
