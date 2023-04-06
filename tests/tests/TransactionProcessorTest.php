<?php

namespace tests;

use App\Provider\HttpBinProvider;
use App\Provider\HttpExchangeRateProvider;
use App\TransactionProcessor;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;

class TransactionProcessorTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testTransactionProcessor(): void
    {
        $binMock = new MockHandler([
            new Response(200, [], json_encode(['country' => ['alpha2' => 'ES']], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode(['country' => ['alpha2' => 'PL']], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode(['country' => ['alpha2' => 'JP']], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode(['country' => ['alpha2' => 'US']], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode(['country' => ['alpha2' => 'GB']], JSON_THROW_ON_ERROR)),
        ]);

        $exchangeRatesMock = new MockHandler([
            new Response(200, [], json_encode(['rates' => ['EUR' => 1]], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode(['rates' => ['USD' => 1.089313]], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode(['rates' => ['JPY' => 143.47129]], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode(['rates' => ['USD' => 1.089313]], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode(['rates' => ['GBP' => 0.876624]], JSON_THROW_ON_ERROR)),
        ]);

        $transactionProcessor = new TransactionProcessor(
            new HttpBinProvider('https://lookup.binlist.net/', new Client([
                'handler' => HandlerStack::create($binMock)
            ])),
            new HttpExchangeRateProvider('https://api.exchangeratesapi.io/latest', new Client([
                'handler' => HandlerStack::create($exchangeRatesMock)
            ]))
        );

        $transactions = $transactionProcessor->processTransactionFromFile('input.txt');

        $this->assertSame([
            ['bin' => '45717360', 'amount' => '100.00', 'currency' => 'EUR', 'commission' => 1.0],
            ['bin' => '516793', 'amount' => '50.00', 'currency' => 'USD', 'commission' => 0.46],
            ['bin' => '45417360', 'amount' => '10000.00', 'currency' => 'JPY', 'commission' => 1.39],
            ['bin' => '41417360', 'amount' => '130.00', 'currency' => 'USD', 'commission' => 2.39],
            ['bin' => '4745030', 'amount' => '2000.00', 'currency' => 'GBP', 'commission' => 45.63],
        ], $transactions);
    }
}