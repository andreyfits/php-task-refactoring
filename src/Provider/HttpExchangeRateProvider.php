<?php

namespace App\Provider;

use App\Exception\ExchangeRateException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpExchangeRateProvider implements ExchangeRateProviderInterface
{
    public function __construct(private readonly string $baseUrl, private readonly Client $httpClient)
    {
    }

    /**
     * @throws ExchangeRateException
     * @throws GuzzleException
     */
    public function getExchangeRate(string $currency): float
    {
        try {
            $response     = $this->httpClient->get($this->baseUrl);
            $exchangeRate = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            return $exchangeRate['rates'][$currency];
        } catch (Exception $e) {
            throw new ExchangeRateException(sprintf('Unable to get exchange rate: %s', $e->getMessage()), $e->getCode(), $e);
        }
    }
}