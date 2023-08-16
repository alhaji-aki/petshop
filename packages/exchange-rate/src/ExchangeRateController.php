<?php

namespace AlhajiAki\ExchangeRate;

use AlhajiAki\ExchangeRate\Exceptions\FailedToGetExchangeRate;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExchangeRateController
{
    public function __invoke(Request $request, ExchangeRateService $service): JsonResponse
    {
        $validator = validator($request->all(), [
            'amount' => ['required', 'numeric'],
            'currency' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->error(422, 'Invalid data submitted', $validator->errors()->toArray());
        }

        try {
            $amount = $service->convert(
                $request->float('amount'),
                $request->filled('currency') ? $request->string('currency')->toString() : 'EUR',
            );

            return response()->json([
                'success' => 1,
                'data' => ['amount' => $amount],
                'error' => null,
                'errors' => [],
                'extra' => [],
            ]);
        } catch (FailedToGetExchangeRate $e) {
            return $this->error(400, $e->getMessage());
        } catch (Exception $e) {
            return $this->error();
        }
    }

    // @phpstan-ignore-next-line
    private function error(int $code = 500, string $error = 'Internal Server Error', array $errors = [], array $trace = []): JsonResponse
    {
        return response()->json([
            'success' => 0,
            'data' => [],
            'error' => $error,
            'errors' => $errors,
            'trace' => $trace,
        ], $code);
    }
}
