<?php

namespace App\Http\Controllers\V1\System\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Abstracts\System\Role\RoleAbstractRequest;
use App\Http\Services\V1\Abstracts\System\Role\RoleAbstractService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly RoleAbstractService $roleService,
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

    public function store(RoleAbstractRequest $request)
    {
        return $this->roleService->store($request);
    }

    public function update(RoleAbstractRequest $request, $id)
    {
        return $this->roleService->update($request, $id);
    }

    public function delete($id)
    {
        return $this->roleService->delete($id);
    }
}