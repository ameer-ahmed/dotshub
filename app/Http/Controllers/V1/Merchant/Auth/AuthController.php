<?php

namespace App\Http\Controllers\V1\Merchant\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Abstracts\Merchant\Auth\SignInAbstractRequest;
use App\Http\Requests\V1\Abstracts\Merchant\Auth\SignUpAbstractRequest;
use App\Http\Services\V1\Abstracts\Merchant\Auth\AuthAbstractService;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthAbstractService $authService,
    )
    {
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