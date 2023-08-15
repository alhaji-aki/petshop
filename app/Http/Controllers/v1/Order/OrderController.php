<?php

namespace App\Http\Controllers\v1\Order;

use Exception;
use App\Models\Order;
use RuntimeException;
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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['payment', 'orderStatus', 'orderProducts' => ['product' => ['brand', 'category']]]);

        return ApiResponse::successResponse(new OrderResource($order));
    }
}
