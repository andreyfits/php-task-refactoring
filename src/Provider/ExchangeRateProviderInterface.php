<?php

namespace App\Provider;

interface ExchangeRateProviderInterface
{
    public function getExchangeRate(string $currency): float;
}