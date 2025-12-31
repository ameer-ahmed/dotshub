<?php

namespace App\Http\Services\V1\Abstracts\System\Branch;

use App\Enums\QueryReturnType;
use App\Http\Helpers\Responser;
use App\Http\Requests\V1\Abstracts\System\Branch\BranchAbstractRequest;
use App\Http\Resources\V1\Abstracts\System\Branch\BranchAbstractResource;
use App\Http\Services\PlatformService;
use App\Repository\Contracts\Tenant\BranchRepositoryInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;

abstract class BranchAbstractService extends PlatformService
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
    )
    {
    }

    public function index()
    {
        $branches = $this->branchRepository->query(
            returnType: QueryReturnType::PAGINATE
        );

        return Responser::success(message: __('Retrieved successfully'), data: BranchAbstractResource::collection($branches)->response()->getData());
    }

    public function getAll()
    {
        $branches = $this->branchRepository->query(
            returnType: QueryReturnType::GET
        );

        return Responser::success(message: __('Retrieved successfully'), data: BranchAbstractResource::collection($branches));
    }

    public function show($id)
    {
        $branch = $this->branchRepository->getById($id);

        return Responser::success(message: __('Retrieved successfully'), data: BranchAbstractResource::make($branch));
    }

    public function store(BranchAbstractRequest $request)
    {
        $data = $request->validated();
        $branch = $this->branchRepository->create($data);

        return Responser::success(message: __('Created successfully'), data: BranchAbstractResource::make($branch));
    }

    public function update(BranchAbstractRequest $request, $id)
    {
        $data = $request->validated();
        $branch = $this->branchRepository->update($id, $data);

        return Responser::success(message: __('Updated successfully'), data: BranchAbstractResource::make($branch));
    }

    public function destroy($id)
    {
        $this->branchRepository->delete($id);

        return Responser::success(message: __('Deleted successfully'));
    }
}
