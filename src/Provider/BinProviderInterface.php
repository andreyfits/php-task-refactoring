<?php

namespace App\Provider;

interface BinProviderInterface
{
    public function lookupBin(string $bin): array;
}