<?php

namespace tests;

use App\Provider\HttpBinProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;

class HttpBinProviderTest extends TestCase
{
    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function testHttpBinProviderCanLookupEuCountry(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['country' => ['alpha2' => 'FR']], JSON_THROW_ON_ERROR))
        ]);

        $handlerStack = HandlerStack::create($mock);

        $binProvider = new HttpBinProvider('https://lookup.binlist.net/', new Client([
            'handler' => $handlerStack
        ]));

        $this->assertSame($binProvider->lookupBin('742203489'), ['country' => ['isEu' => true]]);
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function testHttpBinProviderCanLookupNotEuCountry(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['country' => ['alpha2' => 'AW']], JSON_THROW_ON_ERROR))
        ]);

        $handlerStack = HandlerStack::create($mock);

        $binProvider = new HttpBinProvider('https://lookup.binlist.net/', new Client([
            'handler' => $handlerStack
        ]));

        $this->assertSame($binProvider->lookupBin('52370958'), ['country' => ['isEu' => false]]);
    }
}