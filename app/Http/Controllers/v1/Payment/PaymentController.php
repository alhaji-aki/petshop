<?php

namespace App\Http\Controllers\v1\Payment;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Response\ApiResponse;
use App\Http\Resources\PaymentResource;
use Illuminate\Contracts\Support\Responsable;
use App\Http\Requests\Payment\StorePaymentRequest;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Payment::class, 'payment');
    }

    /**
     * List all payments
     *
     * @OA\Get(
     *     path="/api/v1/payments",
     *     tags={"Payments"},
     *     operationId="listPayments",
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
    public function index(Request $request): JsonResponse|Responsable
    {
        $sortOrder = 'desc';

        if ($request->filled('desc')) {
            $sortOrder = $request->boolean('desc') ? 'desc' : 'asc';
        }

        $sortBy = $request->filled('sortBy') ? $request->string('sortBy')->toString() : 'created_at';

        if (!in_array($sortBy, ['created_at', 'type'])) {
            return ApiResponse::failedResponse('Invalid sort by submitted.', 400);
        }

        $payments = Payment::query()
            ->orderBy($sortBy, $sortOrder)
            ->paginate($request->integer('limit', 15))
            ->withQueryString();

        return PaymentResource::collection($payments);
    }

    /**
     * Create a new payment
     *
     * @OA\Post(
     *     path="/api/v1/payments",
     *     tags={"Payments"},
     *     operationId="createPayment",
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="429", description="Rate limit exceeded"),
     *     @OA\Response(response="500", description="Internal Server Error"),
     *     @OA\RequestBody(ref="#/components/requestBodies/StorePaymentRequest"),
     *     security={
     *         {"bearerAuth": {}}
     *     },
     * )
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $payment = $user->payments()->create((array) $request->validated());

        return ApiResponse::successResponse(['uuid' => $payment->uuid]);
    }

    /**
     * Fetch a payment
     *
     * @OA\Get(
     *     path="/api/v1/payments/{uuid}",
     *     tags={"Payments"},
     *     operationId="getPayment",
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
    public function show(Payment $payment): JsonResponse
    {
        return ApiResponse::successResponse(new PaymentResource($payment));
    }

    /**
     * Delete an existing payment
     *
     * @OA\Delete(
     *     path="/api/v1/payments/{uuid}",
     *     tags={"Payments"},
     *     operationId="deletePayment",
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
    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return ApiResponse::successResponse(['uuid' => $payment->uuid]);
    }
}
