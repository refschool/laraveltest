<?php

namespace App\Services;

class DiscountService
{
    private array $codes = [
        'PROMO10'  => 10,
        'PROMO20'  => 20,
        'BIENVENU' => 15,
    ];

    public function apply(float $amount, string $code): float
    {
        $code = strtoupper(trim($code));

        if (!array_key_exists($code, $this->codes)) {
            throw new \InvalidArgumentException("Code promo '{$code}' invalide.");
        }

        return round($amount * (1 - $this->codes[$code] / 100), 2);
    }

    public function isValid(string $code): bool
    {
        return array_key_exists(strtoupper(trim($code)), $this->codes);
    }

    public function getDiscountPercent(string $code): int
    {
        return $this->codes[strtoupper(trim($code))] ?? 0;
    }
}
