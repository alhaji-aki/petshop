<?php

namespace AlhajiAki\ExchangeRate;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use AlhajiAki\ExchangeRate\Exceptions\FailedToGetExchangeRate;

class ExchangeRateController
{
    public function __invoke(Request $request, ExchangeRateService $service): JsonResponse
    {
        $validator = validator($request->all(), [
            'amount' => ['required', 'numeric'],
            'currency' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Invalid data submitted', 422, $validator->errors()->toArray());
        }

        try {
            return $this->successResponse(
                $service->convert(
                    $request->float('amount'),
                    $request->filled('currency') ? $request->string('currency')->toString() : 'EUR',
                )
            );
        } catch (FailedToGetExchangeRate $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->errorResponse('Internal Server error', 500);
        }
    }

    private function successResponse(float $amount): JsonResponse
    {
        return response()->json([
            'success' => 1,
            'data' => ['amount' => $amount],
            'error' => null,
            'errors' => [],
            'extra' => [],
        ]);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
     * @param array<int|string, mixed> $errors
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
     * @param array<int|string, mixed> $trace
     */
    // @phpstan-ignore-next-line
    private function errorResponse(
        string $error,
        int $status,
        array $errors = [],
        array $trace = []
    ): JsonResponse {
        return response()->json([
            'success' => 0,
            'data' => [],
            'error' => $error,
            'errors' => $errors,
            'trace' => $trace,
        ], $status);
    }
}
