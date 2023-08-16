<?php

namespace AlhajiAki\ExchangeRate;

use AlhajiAki\ExchangeRate\Exceptions\FailedToGetExchangeRate;
use Illuminate\Http\Client\Factory;

class ExchangeRateService
{
    /** @var string default base URL for Data Center's API */
    public const API_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    private Factory $http;

    public function __construct(Factory $http)
    {
        $this->http = $http;
    }

    public function getEndpoint(): string
    {
        return self::API_URL;
    }

    public function convert(float|int $amount, string $currency): float
    {
        $response = $this->http->get($this->getEndpoint());

        if ($response->failed()) {
            throw FailedToGetExchangeRate::because('The service is currently unavailable.');
        }

        libxml_use_internal_errors(true);

        /** @var \SimpleXMLElement|false */
        $xml = simplexml_load_string($response->body());

        if ($xml === false) {
            throw FailedToGetExchangeRate::because('Invalid response received.');
        }

        $encodedXML = json_encode($xml);

        if ($encodedXML === false) {
            throw FailedToGetExchangeRate::because('Invalid response received.');
        }

        $decodedData = json_decode($encodedXML, true);

        if (! $decodedData) {
            throw FailedToGetExchangeRate::because('Invalid response received.');
        }

        /** @var array<string, array> */
        $data = collect($decodedData)->flatten(1)->first(); // @phpstan-ignore-line

        /** @var array|null */
        // @phpstan-ignore-next-line
        $rate = collect($data['Cube'])
            ->pluck('@attributes')
            ->firstWhere('currency', strtoupper($currency));

        if (! $rate) {
            throw FailedToGetExchangeRate::because('Rate not found.');
        }

        return round($amount * $rate['rate'], 2);
    }
}
