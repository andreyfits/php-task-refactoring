<?php

namespace App;

use App\Provider\BinProviderInterface;
use App\Provider\ExchangeRateProviderInterface;
use JsonException;

class TransactionProcessor
{
    public function __construct(
        private readonly BinProviderInterface          $binProvider,
        private readonly ExchangeRateProviderInterface $exchangeRateProvider
    )
    {
    }

    /**
     * @throws JsonException
     */
    public function processTransactionFromFile(string $fileName): array
    {
        $transactions = [];
        $lines = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $transaction     = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            $binDetails      = $this->binProvider->lookupBin($transaction['bin']);
            $isEu            = $binDetails['country']['isEu'];
            $exchangeRate    = $this->exchangeRateProvider->getExchangeRate($transaction['currency']);
            $amountInEur     = $transaction['amount'] / $exchangeRate;
            $commissionRate  = $isEu ? 0.01 : 0.02;
            $commissionInEur = round($amountInEur * $commissionRate, 2);

            $transactions[] = [
                'bin'        => $transaction['bin'],
                'amount'     => $transaction['amount'],
                'currency'   => $transaction['currency'],
                'commission' => $commissionInEur,
            ];
        }

        return $transactions;
    }
}