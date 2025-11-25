<?php

namespace App\Http\Services\V1\Abstracts\Merchant\Auth;

use App\Http\Helpers\Responser;
use App\Http\Requests\V1\Web\Merchant\Auth\SignInRequest;
use App\Http\Requests\V1\Web\Merchant\Auth\SignUpRequest;
use App\Http\Resources\V1\Web\Merchant\User\UserResource;
use App\Http\Services\PlatformService;
use App\Repository\Contracts\DomainRepositoryInterface;
use App\Repository\Contracts\MerchantRepositoryInterface;
use App\Repository\Contracts\Tenant\UserRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

abstract class AuthAbstractService extends PlatformService
{
    public function __construct(
        private readonly MerchantRepositoryInterface $merchantRepository,
        private readonly DomainRepositoryInterface   $domainRepository,
        private readonly UserRepositoryInterface     $userRepository,
    )
    {
    }


    /**
     * @throws \Throwable
     */
    public function signUp(SignUpRequest $request)
    {
        $data = $request->validated();
        $merchant = null;
        $user = null;

        tenancy()->central(function () use (&$merchant, $data) {
            $merchant = $this->merchantRepository->create([
                'name' => $data['merchant_name'],
                'description' => $data['merchant_description'],
            ]);

            $this->domainRepository->create([
                'domain' => $data['merchant_domain'],
                'tenant_id' => $merchant->id,
            ]);
        });

        try {
            tenancy()->initialize($merchant);

            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);
            $user->addRole('merchant_admin');
        } catch (\Throwable $e) {
            try {
                tenancy()->end();
            } catch (\Throwable $ignored) {
            }

            tenancy()->central(function () use ($merchant) {
                DB::connection(config('tenancy.database.central_connection', 'mysql'))
                    ->transaction(function () use ($merchant) {
                        $this->merchantRepository->delete($merchant->id);
                    });
            });

            return Responser::fail(
                status: Response::HTTP_INTERNAL_SERVER_ERROR,
                message: __('Something went wrong')
            );
        } finally {
            try {
                tenancy()->end();
            } catch (\Throwable $ignored) {
            }
        }

        return Responser::success(
            message: __('Created successfully'),
            data: new UserResource($user, false)
        );
    }

    public function signIn(SignInRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $token = auth('user')->attempt($credentials);

        if ($token) {
            return Responser::success(message: __('Successfully signed in'), data: new UserResource(auth('user')->user(), true));
        }

        return Responser::fail(status: 401, message: __('Wrong credentials'));
    }

    public function signOut()
    {
        auth('user')->logout();

        return Responser::success(message: __('Successfully signed out'));
    }

}
