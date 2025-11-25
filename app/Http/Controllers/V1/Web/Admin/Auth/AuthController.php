<?php

namespace App\Http\Controllers\V1\Web\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Web\Admin\Auth\SignInRequest;
use App\Http\Requests\V1\Web\Admin\Auth\SignUpRequest;
use App\Http\Services\V1\Web\Admin\Auth\AuthService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AuthController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly AuthService $authService,
    )
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', only: ['signOut']),
        ];
    }

    public function signUp(SignUpRequest $request) {
        return $this->authService->signUp($request);
    }

    public function signIn(SignInRequest $request) {
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
