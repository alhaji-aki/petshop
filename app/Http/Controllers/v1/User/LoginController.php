<?php

namespace App\Http\Controllers\v1\User;

use Exception;
use OpenApi\Annotations as OA;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Response\ApiResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Actions\Authentication\LoginAction;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * View a user account
     *
     * @OA\Post(
     *     path="/api/v1/user/login",
     *     tags={"User"},
     *     operationId="login",
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="429", description="Rate limit exceeded"),
     *     @OA\Response(response="500", description="Internal Server Error"),
     *     @OA\RequestBody(ref="#/components/requestBodies/LoginRequest"),
     *     security={},
     * )
     */
    public function store(LoginRequest $request, LoginAction $action): JsonResponse
    {
        try {
            $token = $action->execute($request, false);

            return ApiResponse::successResponse(['token' => $token]);
        } catch (ValidationException $th) {
            return ApiResponse::failedResponse(
                "Failed to authenticate user",
                $th->status,
                $th->errors()
            );
        } catch (Exception $th) {
            report($th);

            return ApiResponse::failedResponse(
                "Failed to authenticate user",
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            );
        }
    }
}
