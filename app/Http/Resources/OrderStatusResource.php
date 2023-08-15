<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrderStatus */
class OrderStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
     * @return array<string, mixed>
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function toArray(Request $request): array
    {
        return [
            "uuid" => $this->uuid,
            "title" => $this->title,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
