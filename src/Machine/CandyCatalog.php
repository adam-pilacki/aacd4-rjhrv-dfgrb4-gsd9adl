<?php

namespace App\Machine;

final class CandyCatalog implements CandyCatalogInterface
{
    private const CANDIES = [
        'caramels' => 4.99,
        'lollipop' => 2.99,
        'mince drops' => 0.69,
        'chewing gum' => 1.99,
        'licorice' => 3.59,
    ];

    public function getAvailableCandies(): array
    {
        return self::CANDIES;
    }

    public function getCandyNames(): array
    {
        return array_keys(self::CANDIES);
    }

    public function getCandyPrice(string $type): float
    {
        if (!$this->isValidCandyType($type)) {
            throw new \InvalidArgumentException("Invalid candy type: {$type}");
        }

        return self::CANDIES[$type];
    }

    public function isValidCandyType(string $type): bool
    {
        return array_key_exists($type, self::CANDIES);
    }
}
