<?php

namespace tests;

use App\Exception\ExchangeRateException;
use App\Provider\HttpExchangeRateProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;

class HttpExchangeRateProviderTest extends TestCase
{
    /**
     * @throws GuzzleException
     * @throws ExchangeRateException
     * @throws JsonException
     */
    public function testExchangeRateProvider(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['rates' => ['EUR' => '1000']], JSON_THROW_ON_ERROR)),
        ]);

        $handleStack = HandlerStack::create($mock);

        $exchangeRateProvider = new HttpExchangeRateProvider('https://api.exchangeratesapi.io/latest', new Client([
            'handler' => $handleStack,
        ]));

        $this->assertSame(1000.0, $exchangeRateProvider->getExchangeRate('EUR'));
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function testExchangeRateProviderErrorHandle(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['rates' => []], JSON_THROW_ON_ERROR)),
        ]);

        $handleStack = HandlerStack::create($mock);

        $exchangeRateProvider = new HttpExchangeRateProvider('https://api.exchangeratesapi.io/latest', new Client([
            'handler' => $handleStack,
        ]));

        $this->expectException(ExchangeRateException::class);
        $exchangeRateProvider->getExchangeRate('EUR');
    }
}