<?php

namespace App\Http\Controllers\v1\User;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\Response\ApiResponse;
use Illuminate\Contracts\Support\Responsable;

class OrderController extends Controller
{
    /**
     * List all orders for a user
     *
     * @OA\Get(
     *     path="/api/v1/user/orders",
     *     tags={"User"},
     *     operationId="userOrders",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sortBy",
     *         in="query",
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         @OA\Schema(
     *             type="bool",
     *             enum={"true", "false"},
     *         )
     *     ),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="400", description="Bad request"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="500", description="Internal Server Error"),
     *     security={
     *         {"bearerAuth": {}}
     *     },
     * )
     */
    public function __invoke(Request $request): JsonResponse|Responsable
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $sortOrder = 'desc';

        if ($request->filled('desc')) {
            $sortOrder = $request->boolean('desc') ? 'desc' : 'asc';
        }

        $sortBy = $request->filled('sortBy') ? $request->string('sortBy')->toString() : 'created_at';

        if (!in_array($sortBy, ['created_at'])) {
            return ApiResponse::failedResponse('Invalid sort by submitted.', 400);
        }

        $orders = $user->orders()->getQuery()
            ->orderBy($sortBy, $sortOrder)
            ->paginate($request->integer('limit', 15))
            ->withQueryString();

        return OrderResource::collection($orders);
    }
}
