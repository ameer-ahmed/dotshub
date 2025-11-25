<?php

namespace App\Http\Resources\V1\Web\Merchant\User;

use App\Http\Resources\V1\Web\Merchant\Merchant\MerchantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function __construct($resource, private readonly bool $withToken)
    {
        parent::__construct($resource);
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'merchant' => new MerchantResource($this->merchant),
            'token' => $this->when($this->withToken, $this->token()),
        ];
    }
}
