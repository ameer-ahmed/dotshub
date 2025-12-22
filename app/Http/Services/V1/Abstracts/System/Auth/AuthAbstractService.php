<?php

namespace App\Http\Services\V1\Abstracts\System\Auth;

use App\Http\Helpers\Responser;
use App\Http\Requests\V1\Web\System\Auth\SignInRequest;
use App\Http\Requests\V1\Web\System\Auth\SignUpRequest;
use App\Http\Resources\V1\Web\System\User\UserResource;
use App\Http\Services\PlatformService;
use App\Repository\Contracts\DomainRepositoryInterface;
use App\Repository\Contracts\TenantRepositoryInterface;
use App\Repository\Contracts\Tenant\UserRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

abstract class AuthAbstractService extends PlatformService
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
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
        $tenant = null;
        $user = null;

        tenancy()->central(function () use (&$tenant, $data) {
            $tenant = $this->tenantRepository->create([
                'name' => $data['tenant_name'],
                'description' => $data['tenant_description'],
            ]);

            $this->domainRepository->create([
                'domain' => $data['tenant_domain'],
                'tenant_id' => $tenant->id,
            ]);
        });

        try {
            tenancy()->initialize($tenant);

            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);
            $user->addRole('system_admin');

            $response = Responser::success(
                message: __('Created successfully'),
                data: new UserResource($user, false)
            );

            tenancy()->end();

            return $response;
        } catch (\Throwable $e) {
            try {
                tenancy()->end();
            } catch (\Throwable $ignored) {
            }

            tenancy()->central(function () use ($tenant) {
                DB::connection(config('tenancy.database.central_connection', 'mysql'))
                    ->transaction(function () use ($tenant) {
                        $this->tenantRepository->delete($tenant->id);
                    });
            });

            return Responser::fail(
                status: Response::HTTP_INTERNAL_SERVER_ERROR,
                message: __('Something went wrong')
            );
        }
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
