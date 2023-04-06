<?php

use App\Provider\HttpBinProvider;
use App\Provider\HttpExchangeRateProvider;
use App\TransactionProcessor;
use GuzzleHttp\Client;

require_once __DIR__ . '/vendor/autoload.php';

$httpClient = new Client();
$transactionProcessor = new TransactionProcessor(
    new HttpBinProvider('https://lookup.binlist.net/', $httpClient),
    new HttpExchangeRateProvider(
        'https://api.apilayer.com/exchangerates_data/latest',
        new Client([
            'headers' => [
                'apikey' => '4qBqfx24t4frCBBmeUDO2d9IUKQgJDHV'
            ]
        ])
    )
);

$inputFileName = $argv[1] ?? 'input.txt';
$transactions = $transactionProcessor->processTransactionFromFile(__DIR__ . '/' . $inputFileName);

foreach ($transactions as $transaction) {
    echo $transaction['commission'] . "\n";
}
