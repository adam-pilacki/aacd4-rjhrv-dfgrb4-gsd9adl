<?php

namespace App\Machine;

interface CandyCatalogInterface
{
    /**
     * Get all available candies with their prices
     */
    public function getAvailableCandies(): array;

    /**
     * Get array of candy names
     */
    public function getCandyNames(): array;

    /**
     * Get price for specific candy type
     */
    public function getCandyPrice(string $type): float;

    /**
     * Check if candy type is valid
     */
    public function isValidCandyType(string $type): bool;
}
