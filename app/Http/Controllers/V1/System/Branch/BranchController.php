<?php

namespace App\Http\Controllers\V1\System\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Abstracts\System\Branch\BranchAbstractRequest;
use App\Http\Services\V1\Abstracts\System\Branch\BranchAbstractService;

class BranchController extends Controller
{
    public function __construct(
        private readonly BranchAbstractService $branchAbstractService,
    ) {}

    public function index()
    {
        return $this->branchAbstractService->index();
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
