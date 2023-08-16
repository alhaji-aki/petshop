<?php

namespace AlhajiAki\ExchangeRate\Tests;

use Illuminate\Support\Facades\Http;

class ExchangeRateControllerTest extends TestCase
{
    public function test_returns_a_valid_response()
    {
        $responseXML = file_get_contents(__DIR__ . '/stubs/response.xml');

        Http::fake([
            'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response($responseXML, 200),
        ]);

        $this->get(route('exchange-rate:index', [
            'amount' => 100,
            'currency' => 'USD',
        ]))
            ->assertSuccessful()
            ->assertJsonFragment(['amount' => 109.26]);
    }

    public function test_returns_validation_error_if_amount_is_not_given()
    {
        $this->get(route('exchange-rate:index'))
            ->assertUnprocessable();

        $this->get(route('exchange-rate:index', ['amount' => 'hi']))
            ->assertUnprocessable();
    }
}
