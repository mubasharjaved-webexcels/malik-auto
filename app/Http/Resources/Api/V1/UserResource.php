<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->user_type,
            'phone' => $this->phone,
            'country_id' => $this->country_id,
            'created_at' => $this->created_at,
        ];
    }
}
