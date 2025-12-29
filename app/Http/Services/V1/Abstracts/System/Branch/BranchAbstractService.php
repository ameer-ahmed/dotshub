<?php

namespace App\Http\Services\V1\Abstracts\System\Branch;

use App\Enums\QueryReturnType;
use App\Http\Helpers\Responser;
use App\Http\Requests\V1\Abstracts\System\Branch\BranchAbstractRequest;
use App\Http\Resources\V1\Abstracts\System\Branch\BranchAbstractResource;
use App\Http\Services\PlatformService;
use App\Repository\Contracts\Tenant\BranchRepositoryInterface;
use Illuminate\Http\Request;

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

        return Responser::success(data: BranchAbstractResource::collection($branches)->response()->getData());
    }

    public function getAll()
    {
        $branches = $this->branchRepository->query(
            returnType: QueryReturnType::GET
        );


    }

    public function store(BranchAbstractRequest $request)
    {

    }

    public function update(BranchAbstractRequest $request, $id)
    {

    }

    public function destroy($id)
    {

    }
}
