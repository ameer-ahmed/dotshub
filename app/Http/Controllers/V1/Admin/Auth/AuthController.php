<?php

namespace App\Http\Controllers\V1\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Abstracts\Admin\Auth\SignInAbstractRequest;
use App\Http\Requests\V1\Abstracts\Admin\Auth\SignUpAbstractRequest;
use App\Http\Services\V1\Abstracts\Admin\Auth\AuthAbstractService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AuthController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly AuthAbstractService $authService,
    )
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', only: ['signOut']),
        ];
    }

    public function signUp(SignUpAbstractRequest $request) {
        return $this->authService->signUp($request);
    }

    public function signIn(SignInAbstractRequest $request) {
        return $this->authService->signIn($request);
    }

    public function signOut()
    {
        return $this->authService->signOut();
    }

    public function whatIsMyPlatform()
    {
        return $this->authService->whatIsMyPlatform();
    }
}