<?php

namespace App\Http\Requests\Auth;

use OpenApi\Annotations as OA;
use Illuminate\Http\JsonResponse;
use App\Services\Response\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
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
     *     request="LoginRequest",
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/x-www-form-urlencoded",
     *         @OA\Schema(
     *              required={"email", "password"},
     *              @OA\Property(
     *                  description="User email",
     *                  property="email",
     *                  type="string",
     *                  format="email"
     *              ),
     *              @OA\Property(
     *                  description="User password",
     *                  property="password",
     *                  type="string",
     *                  format="password"
     *              )
     *         )
     *     )
     * )
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            ApiResponse::failedResponse(
                "Failed to authenticate user",
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                $errors
            )
        );
    }
}
