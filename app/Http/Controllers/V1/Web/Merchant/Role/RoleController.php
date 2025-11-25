<?php

namespace App\Http\Controllers\V1\Web\Merchant\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Web\Merchant\Role\RoleRequest;
use \App\Http\Services\V1\Web\Merchant\Role\RoleService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly RoleService $roleService,
    ) {}

    public static function middleware()
    {
        return [
            new Middleware('permission:create-roles|guard:user', ['store']),
            new Middleware('permission:update-roles|guard:user', ['update']),
            new Middleware('permission:delete-roles|guard:user', ['delete']),
        ];
    }

    public function index()
    {
        return $this->roleService->index();
    }

    public function store(RoleRequest $request)
    {
        return $this->roleService->store($request);
    }

    public function update(RoleRequest $request, $id)
    {
        return $this->roleService->update($request, $id);
    }

    public function delete($id)
    {
        return $this->roleService->delete($id);
    }
}
