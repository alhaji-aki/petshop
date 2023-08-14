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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $payment = $user->payments()->create((array) $request->validated());

        return ApiResponse::successResponse(['uuid' => $payment->uuid]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): JsonResponse
    {
        return ApiResponse::successResponse(new PaymentResource($payment));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return ApiResponse::successResponse(['uuid' => $payment->uuid]);
    }
}
