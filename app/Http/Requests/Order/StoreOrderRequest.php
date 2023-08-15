<?php

namespace App\Http\Requests\Order;

use App\Models\Payment;
use App\Models\OrderStatus;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Services\Response\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        return [
            'order_status_uuid' => ['required', 'string', Rule::exists(OrderStatus::class, 'uuid')],
            'payment_uuid' => ['required', 'string', Rule::exists(Payment::class, 'uuid')->where('user_id', $user->id)],
            'products' => ['required', 'array', 'min:1'],
            'products.*.uuid' => ['required', 'string', 'distinct'],
            'products.*.quantity' => ['required', 'integer', 'gt:0'],
            'address.billing' => ['required', 'string', 'max:255'],
            'address.shipping' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'order_status_uuid' => 'order status',
            'payment_uuid' => 'payment',
            'products.*.uuid' => 'product :position',
            'products.*.quantity' => 'product quantity :position',
            'address.billing' => 'billing address',
            'address.shipping' => 'shipping address',
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
     * @return array<int, mixed>
     */
    public function after(): array
    {
        /**
         * @var array<int, array{uuid:string, quantity:int}> $products
         */
        $products = (array) $this->input('products');

        return [
            new ValidateProducts($products),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $execption = (new ValidationException($validator));
        $errors = $execption->errors();

        throw new HttpResponseException(
            ApiResponse::failedResponse(
                $execption->getMessage(),
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                $errors
            )
        );
    }
}
