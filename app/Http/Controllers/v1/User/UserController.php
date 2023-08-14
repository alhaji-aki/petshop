<?php

namespace App\Http\Controllers\v1\User;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\Response\ApiResponse;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->load('avatar');

        return ApiResponse::successResponse(new UserResource($user));
    }
}
