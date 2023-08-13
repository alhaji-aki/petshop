<?php

namespace App\Http\Controllers\v1\User;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Response\ApiResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Actions\Authentication\LoginAction;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Store a newly created resource in storage.
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
