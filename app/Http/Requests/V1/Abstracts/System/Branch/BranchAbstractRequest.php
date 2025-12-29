<?php

namespace App\Http\Requests\V1\Abstracts\System\Branch;

use App\Http\Requests\PlatformRequest;

abstract class BranchAbstractRequest extends PlatformRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // TODO: Add validation rules
        ];
    }
}
