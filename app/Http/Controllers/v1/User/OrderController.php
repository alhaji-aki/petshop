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
     * Display a listing of the resource.
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
