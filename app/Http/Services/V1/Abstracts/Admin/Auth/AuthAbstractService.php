<?php

namespace App\Http\Services\V1\Abstracts\Admin\Auth;

use App\Http\Helpers\Responser;
use App\Http\Requests\V1\Web\Admin\Auth\SignInRequest;
use App\Http\Requests\V1\Web\Admin\Auth\SignUpRequest;
use App\Http\Resources\V1\Web\Admin\User\UserResource;
use App\Http\Services\PlatformService;
use App\Repository\Contracts\Tenant\UserRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

abstract class AuthAbstractService extends PlatformService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    )
    {
    }

    public function signUp(SignUpRequest $request) {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            $user = $this->userRepository->create($data);

            DB::commit();
            return Responser::success(message: __('created successfully'), data: new UserResource($user, false));
        } catch (Exception $e) {
            DB::rollBack();
//            dd($e);
            return Responser::fail(message: __('Something went wrong'));
        }
    }

    public function signIn(SignInRequest $request) {
        $credentials = $request->only('email', 'password');
        $token = auth('admin')->attempt($credentials);
        if ($token) {
            return Responser::success(message: __('Successfully authenticated'), data: new UserResource(auth('admin')->user(), true));
        }

        return Responser::fail(status: 401, message: __('wrong credentials'));
    }

    public function signOut() {
        auth('admin')->logout();
        return Responser::success(message: __('Successfully loggedOut'));
    }

}
