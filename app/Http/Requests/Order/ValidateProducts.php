<?php

namespace App\Http\Requests\Order;

use App\Models\Product;
use Illuminate\Validation\Validator;

class ValidateProducts
{
    /**
     * @param array<int, array{uuid:string, quantity:int}> $products
     */
    public function __construct(
        private readonly array $products,
    ) {
    }

    public function __invoke(Validator $validator): void
    {
        if ($validator->errors()->hasAny(['products.*', 'products.*.uuid'])) {
            return;
        }

        $uuids = collect((array) data_get($this->products, '*.uuid'));

        $products = Product::query()
            ->whereIn('uuid', $uuids->filter()->toArray())
            ->pluck('uuid');

        /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter */
        $uuids->diff($products)->each(function ($id, $i) use ($validator): void {
            $validator->errors()->add("products.{$i}.uuid", "Invalid Product");
        });
    }
}
