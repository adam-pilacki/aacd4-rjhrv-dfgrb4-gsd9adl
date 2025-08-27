<?php

namespace App\Machine;

use App\Exception\InsufficientPaymentException;

final class CandyMachine implements MachineInterface
{
    private CandyCatalog $catalog;
    // change available coins as you want
    private array $availableCoins = [2.00, 1.00, 0.50, 0.20, 0.10, 0.05, 0.02, 0.01];

    public function __construct(CandyCatalog $catalog)
    {
        $this->catalog = $catalog;
    }

    public function execute(PurchaseTransactionInterface $purchaseTransaction): PurchasedItemInterface
    {
        $candyType = $purchaseTransaction->getType();
        $quantity = $purchaseTransaction->getItemQuantity();
        $paidAmount = $purchaseTransaction->getPaidAmount();

        // Calculate total cost
        $unitPrice = $this->catalog->getCandyPrice($candyType);
        $totalCost = round($unitPrice * $quantity, 2);

        // Check if payment is sufficient
        if ($paidAmount < $totalCost) {
            throw new InsufficientPaymentException(
                "Insufficient payment. Required: {$totalCost}€, Provided: {$paidAmount}€"
            );
        }

        // Calculate change
        $changeAmount = $paidAmount - $totalCost;
        $change = $this->calculateChange($changeAmount);

        return new PurchasedItem($candyType, $quantity, $totalCost, $change);
    }

    private function calculateChange(float $amount): array
    {
        if ($amount <= 0) {
            return [];
        }

        $change = [];
        $remainingAmount = round($amount, 2);

        foreach ($this->availableCoins as $coin) {
            if ($remainingAmount >= $coin) {
                $count = (int) floor($remainingAmount / $coin);
                $change[(string) $coin] = $count;
                $remainingAmount = round($remainingAmount - ($coin * $count), 2);
            }

            if ($remainingAmount <= 0) {
                break;
            }
        }

        // Additional check for very small amounts (rounding issue)
        if ($remainingAmount > 0 && $remainingAmount < 0.01) {
            $remainingAmount = 0;
        }

        return $change;
    }
}
