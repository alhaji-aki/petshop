<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentTypeEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(PaymentTypeEnum::class)],
            "details.first_name" => [
                'exclude_unless:type,' . PaymentTypeEnum::CashOnDelivery->value,
                'required', 'string', 'max:255',
            ],
            "details.last_name" => [
                'exclude_unless:type,' . PaymentTypeEnum::CashOnDelivery->value,
                'required', 'string', 'max:255',
            ],
            "details.address" => [
                'exclude_unless:type,' . PaymentTypeEnum::CashOnDelivery->value,
                'required', 'string', 'max:255',
            ],
            "details.swift" => [
                'exclude_unless:type,' . PaymentTypeEnum::BankTransfer->value,
                'required', 'string', 'max:255',
            ],
            "details.iban" => [
                'exclude_unless:type,' . PaymentTypeEnum::BankTransfer->value,
                'required', 'string', 'max:255',
            ],
            "details.name" => [
                'exclude_unless:type,' . PaymentTypeEnum::BankTransfer->value,
                'required', 'string', 'max:255',
            ],
            "details.holder_name" => [
                'exclude_unless:type,' . PaymentTypeEnum::CreditCard->value,
                'required', 'string', 'max:255',
            ],
            "details.number" => [
                'exclude_unless:type,' . PaymentTypeEnum::CreditCard->value,
                'required', 'string', 'max:255',
            ],
            "details.ccv" => [
                'exclude_unless:type,' . PaymentTypeEnum::CreditCard->value,
                'required', 'digits:3', 'max:255',
            ],
            "details.expire_date" => [
                'exclude_unless:type,' . PaymentTypeEnum::CreditCard->value,
                'required', 'date_format:m/y', 'after:now',
            ],
        ];
    }
}
