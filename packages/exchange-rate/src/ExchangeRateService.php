<?php

namespace AlhajiAki\ExchangeRate;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\RequestException;
use AlhajiAki\ExchangeRate\Exceptions\FailedToGetExchangeRate;

class ExchangeRateService
{
    public const API_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    public function __construct(
        private Factory $http
    ) {
    }

    public function getEndpoint(): string
    {
        return self::API_URL;
    }

    public function convert(float|int $amount, string $currency): float
    {
        try {
            $response = $this->http->get($this->getEndpoint())->throwUnlessStatus(200);
        } catch (RequestException $th) {
            throw FailedToGetExchangeRate::because('The service is currently unavailable.');
        }

        if ($response->failed()) {
            throw FailedToGetExchangeRate::because('The service is currently unavailable.');
        }

        libxml_use_internal_errors(true);

        /** @var \SimpleXMLElement|false $xml */
        $xml = simplexml_load_string($response->body());

        if ($xml === false) {
            throw FailedToGetExchangeRate::because('Invalid response received.');
        }

        $encodedXML = json_encode($xml);

        if ($encodedXML === false) {
            throw FailedToGetExchangeRate::because('Invalid response received.');
        }

        $decodedData = json_decode($encodedXML, true);

        if (!$decodedData) {
            throw FailedToGetExchangeRate::because('Invalid response received.');
        }

        /** @var array<string, array> $data */
        $data = collect($decodedData)->flatten(1)->first(); // @phpstan-ignore-line

        // @phpstan-ignore-next-line
        /** @var array|null $rate */
        $rate = collect($data['Cube'])
            ->pluck('@attributes')
            ->firstWhere('currency', strtoupper($currency));

        if (!$rate) {
            throw FailedToGetExchangeRate::because('Rate not found.');
        }

        return round($amount * $rate['rate'], 2);
    }
}
