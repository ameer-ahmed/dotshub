<?php

namespace App\Http\Services\V1\Web\System\Role;

use App\Enums\Platform;
use App\Http\Helpers\Responser;
use App\Http\Requests\V1\Web\System\Role\RoleRequest;
use App\Http\Resources\V1\Web\System\Role\RoleResource;
use App\Http\Services\V1\Abstracts\System\Role\RoleAbstractService;
use App\Repository\Contracts\Tenant\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RoleService extends RoleAbstractService
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }

    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
    )
    {
    }

    public function index()
    {
        $roles = $this->roleRepository->getAll();

        if ($roles->isEmpty()) {
            return Responser::fail(
                Response::HTTP_NOT_FOUND,
                __('No available roles found. If the problem persists, please contact technical support.'),
            );
        }

        return Responser::success(data: RoleResource::collection($roles));
    }

    public function store(RoleRequest $request)
    {
        $user = auth('user')->user();
        $permissions = $request->input('permissions', []);

        if (!empty($permissions) && !$user->hasPermission($permissions, requireAll: true)) {
            return Responser::fail(
                Response::HTTP_FORBIDDEN,
                __('Forbidden action. If the problem persists, please contact technical support.')
            );
        }

        $payload = [
            ...$request->only(['name', 'display_name', 'description']),
            'created_by' => $user->id,
            'is_private' => true,
            'is_editable' => true,
        ];

        try {
            $role = DB::transaction(function () use ($payload, $permissions) {
                $role = $this->roleRepository->create($payload);

                if (!empty($permissions)) {
                    $role->syncPermissions($permissions);
                }

                return $role;
            });

            return Responser::success(
                message: __('Role created successfully.'),
                data: RoleResource::make($role)
            );
        } catch (Throwable $e) {
            return Responser::fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                __('Something went wrong. If the problem persists, please contact technical support.'),
                throwable: $e
            );
        }
    }

    public function update(RoleRequest $request, $id)
    {
        $user = auth('user')->user();
        $role = $this->roleRepository->getById($id);
        $permissions = $request->input('permissions', []);

        if (
            (!$user->hasRole('system_admin') && $user->id !== $role->created_by)
            || (!empty($permissions) && !$user->hasPermission($permissions, requireAll: true))
        ) {
            return Responser::fail(
                Response::HTTP_FORBIDDEN,
                __('Forbidden action. If the problem persists, please contact technical support.')
            );
        }

        $payload = $request->safe()->only(['display_name', 'description']);

        try {
            $role = DB::transaction(function () use ($id, $payload, $permissions) {
                $role = $this->roleRepository->update($id, $payload);

                $role->syncPermissions($permissions);

                return $role;
            });

            return Responser::success(
                message: __('Role updated successfully.'),
                data: RoleResource::make($role)
            );
        } catch (Throwable $e) {
            return Responser::fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                __('Something went wrong. If the problem persists, please contact technical support.'),
                throwable: $e
            );
        }
    }

    public function delete($id)
    {
        $user = auth('user')->user();
        $role = $this->roleRepository->getById($id);

        if (!$user->hasRole('system_admin') && $user->id !== $role->created_by) {
            return Responser::fail(
                Response::HTTP_FORBIDDEN,
                __('Forbidden action. If the problem persists, please contact technical support.')
            );
        }

        try {
            $this->roleRepository->delete($id);

            return Responser::success(
                message: __('Role deleted successfully.'),
            );
        } catch (Throwable $e) {
            return Responser::fail(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                __('Something went wrong. If the problem persists, please contact technical support.'),
                throwable: $e
            );
        }

    }
}
