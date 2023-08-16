<?php

namespace App\Http\Controllers\v1\User;

use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\Response\ApiResponse;

class UserController extends Controller
{
    /**
     * View a user account
     *
     * @OA\Get(
     *     path="/api/v1/user",
     *     tags={"User"},
     *     operationId="getUser",
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="500", description="Internal Server Error"),
     *     security={
     *         {"bearerAuth": {}}
     *     },
     * )
     */
    public function show(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->load('avatar');

        return ApiResponse::successResponse(new UserResource($user));
    }
}
