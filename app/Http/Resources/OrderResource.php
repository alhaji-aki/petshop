<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderResource extends JsonResource
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
            "user" => new UserResource($this->whenLoaded('user')),
            "order_status" => new OrderStatusResource($this->whenLoaded('orderStatus')),
            "payment" => new PaymentResource($this->whenLoaded('payment')),
            'products' => OrderProductResource::collection($this->whenLoaded('orderProducts')),
            "address" => $this->address,
            "amount" => $this->amount,
            "delivery_fee" => $this->delivery_fee,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "shipped_at" => $this->shipped_at,
        ];
    }
}
