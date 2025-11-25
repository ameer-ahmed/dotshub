<?php

namespace App\Http\Controllers\V1\Web\Merchant\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Web\Merchant\Auth\SignInRequest;
use App\Http\Requests\V1\Web\Merchant\Auth\SignUpRequest;
use App\Http\Services\V1\Web\Merchant\Auth\AuthService;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    )
    {
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
