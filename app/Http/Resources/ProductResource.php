<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
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
            "slug" => $this->slug,
            "category" => new CategoryResource($this->whenLoaded('category')),
            "price" => $this->price,
            "description" => $this->description,
            "brand" => new BrandResource($this->whenLoaded('brand')),
            "image" => new FileResource($this->whenLoaded('image')),
            "metadata" => $this->metadata,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
