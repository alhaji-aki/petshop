<?php

namespace AlhajiAki\ExchangeRate\Tests;

use AlhajiAki\ExchangeRate\Exceptions\FailedToGetExchangeRate;
use AlhajiAki\ExchangeRate\ExchangeRateService;
use Illuminate\Support\Facades\Http;

class ExchangeRateServiceTest extends TestCase
{
    public function test_converts_amount_correctly()
    {
        $responseXML = file_get_contents(__DIR__ . '/stubs/response.xml');

        Http::fake([
            'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response($responseXML, 200),
        ]);

        $service = app(ExchangeRateService::class);

        $this->assertEquals(1.09, $service->convert(1, 'USD'));
    }

    public function test_throws_exception_if_api_is_unavailable()
    {
        Http::fake([
            'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response('', 500),
        ]);

        $service = app(ExchangeRateService::class);

        $this->expectException(FailedToGetExchangeRate::class);

        $service->convert(1, 'USD');
    }

    public function test_throws_exception_if_response_is_not_a_valid_xml()
    {
        Http::fake([
            'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response('invalid xml', 200),
        ]);

        $service = app(ExchangeRateService::class);

        $this->expectException(FailedToGetExchangeRate::class);

        $service->convert(1, 'USD');
    }

    public function test_unsupported_currency()
    {
        $responseXML = file_get_contents(__DIR__ . '/stubs/response.xml');

        Http::fake([
            'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml' => Http::response($responseXML, 200),
        ]);

        $service = app(ExchangeRateService::class);

        $this->expectException(FailedToGetExchangeRate::class);

        $service->convert(1, 'ASD');
    }
}
