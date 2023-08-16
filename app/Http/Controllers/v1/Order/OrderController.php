<?php

namespace App\Http\Controllers\v1\Order;

use Exception;
use App\Models\Order;
use RuntimeException;
use OpenApi\Annotations as OA;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\Response\ApiResponse;
use App\Actions\Order\CreateOrderAction;
use App\Http\Requests\Order\StoreOrderRequest;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Order::class, 'order');
    }

    /**
     * Create a new order
     *
     * @OA\Post(
     *     path="/api/v1/orders",
     *     tags={"Orders"},
     *     operationId="createOrder",
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="429", description="Rate limit exceeded"),
     *     @OA\Response(response="500", description="Internal Server Error"),
     *     @OA\RequestBody(ref="#/components/requestBodies/StoreOrderRequest"),
     *     security={
     *         {"bearerAuth": {}}
     *     },
     * )
     */
    public function store(StoreOrderRequest $request, CreateOrderAction $createOrderAction): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /**
         * @var array{
         *   order_status_uuid: string,
         *   payment_uuid: string,
         *   products: array<int, array{uuid:string, quantity:int}>,
         *   address: array<string, string>
         * } $data
         */
        $data = (array) $request->validated();

        try {
            $order = $createOrderAction->execute($user, $data);

            return ApiResponse::successResponse(new OrderResource($order));
        } catch (RuntimeException $th) {
            return ApiResponse::failedResponse($th->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (Exception $th) {
            report($th);

            return ApiResponse::failedResponse('Failed to create your order', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Fetch a order
     *
     * @OA\Get(
     *     path="/api/v1/orders/{uuid}",
     *     tags={"Orders"},
     *     operationId="getOrder",
     *     @OA\Parameter(
     *         name="uuid",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Page not found"),
     *     @OA\Response(response="500", description="Internal Server Error"),
     *     security={
     *         {"bearerAuth": {}}
     *     },
     * )
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['payment', 'orderStatus', 'orderProducts' => ['product' => ['brand', 'category']]]);

        return ApiResponse::successResponse(new OrderResource($order));
    }
}
