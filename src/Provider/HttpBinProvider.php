<?php

namespace App\Provider;

use App\Helper\CountryHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use RuntimeException;

class HttpBinProvider implements BinProviderInterface
{
    public function __construct(private readonly string $baseUrl, private readonly Client $httpClient)
    {
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function lookupBin(string $bin): array
    {
        $response = $this->httpClient->get($this->baseUrl . $bin);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Unable to lookup BIN: ' . $bin);
        }

        $binDetails = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return [
            'country' => [
                'isEu' => CountryHelper::isEu($binDetails['country']['alpha2']),
            ]
        ];
    }
}