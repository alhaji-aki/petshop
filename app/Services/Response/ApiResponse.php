<?php

namespace App\Services\Response;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
     * @param array<int|string, mixed> $data
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
     * @param array<int|string, mixed> $extra
     */
    public static function successResponse(array $data, array $extra = []): JsonResponse
    {
        return response()->json([
            'success' => 1,
            'data' => $data,
            'error' => null,
            'errors' => [],
            'extra' => $extra,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * @param array<int|string, int|string|array> $errors
     * @param array<int|string, int|string|array> $trace
     */
    public static function failedResponse(
        string $error,
        int $status,
        array $errors = [],
        array $trace = []
    ): JsonResponse {
        return response()->json([
            'success' => 0,
            'data' => [],
            "error" => $error,
            'errors' => $errors,
            'trace' => $trace,
        ], $status);
    }
}
