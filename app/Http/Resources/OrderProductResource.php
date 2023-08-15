<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrderProduct */
class OrderProductResource extends JsonResource
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
            "order" => new OrderResource($this->whenLoaded('order')),
            "product" => new ProductResource($this->whenLoaded('product')),
            "quantity" => $this->quantity,
            "unit_price" => $this->unit_price,
            "amount" => $this->amount,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
