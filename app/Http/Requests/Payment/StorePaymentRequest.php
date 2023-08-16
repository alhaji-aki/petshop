<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentTypeEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Enum;
use App\Services\Response\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePaymentRequest extends FormRequest
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
     * @OA\RequestBody(
     *     request="StorePaymentRequest",
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/x-www-form-urlencoded",
     *         @OA\Schema(
     *              required={"type", "details"},
     *              @OA\Property(
     *                  description="Payment type",
     *                  property="type",
     *                  type="string",
     *                  enum={"credit_card", "cash_on_delivery", "bank_transfer"},
     *              ),
     *              @OA\Property(
     *                  description="Review documentation for the payment type JSON format",
     *                  property="details",
     *                  type="object",
     *              ),
     *         )
     *     )
     * )
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $cashOnDelivery = PaymentTypeEnum::CashOnDelivery->value;
        $bankTransfer = PaymentTypeEnum::BankTransfer->value;
        $creditCard = PaymentTypeEnum::CreditCard->value;

        return [
            'type' => ['required', new Enum(PaymentTypeEnum::class)],
            "details.first_name" => ['exclude_unless:type,' . $cashOnDelivery, 'required', 'string', 'max:255'],
            "details.last_name" => ['exclude_unless:type,' . $cashOnDelivery, 'required', 'string', 'max:255'],
            "details.address" => ['exclude_unless:type,' . $cashOnDelivery, 'required', 'string', 'max:255'],
            "details.swift" => ['exclude_unless:type,' . $bankTransfer, 'required', 'string', 'max:255'],
            "details.iban" => ['exclude_unless:type,' . $bankTransfer, 'required', 'string', 'max:255'],
            "details.name" => ['exclude_unless:type,' . $bankTransfer, 'required', 'string', 'max:255'],
            "details.holder_name" => ['exclude_unless:type,' . $creditCard, 'required', 'string', 'max:255'],
            "details.number" => ['exclude_unless:type,' . $creditCard, 'required', 'string', 'max:255'],
            "details.ccv" => ['exclude_unless:type,' . $creditCard, 'required', 'digits:3', 'max:255'],
            "details.expire_date" => ['exclude_unless:type,' . $creditCard, 'required', 'date_format:m/y', 'after:now'],
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
