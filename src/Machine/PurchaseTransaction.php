<?php

namespace App\Machine;

final class PurchaseTransaction implements PurchaseTransactionInterface
{
    private string $type;
    private int $itemQuantity;
    private float $paidAmount;

    public function __construct(string $type, int $itemQuantity, float $paidAmount)
    {
        $this->validateType($type);
        $this->validateItemQuantity($itemQuantity);
        $this->validatePaidAmount($paidAmount);

        $this->type = $type;
        $this->itemQuantity = $itemQuantity;
        $this->paidAmount = $paidAmount;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getItemQuantity(): int
    {
        return $this->itemQuantity;
    }

    public function getPaidAmount(): float
    {
        return $this->paidAmount;
    }

    private function validateType(string $type): void
    {
        if (empty(trim($type))) {
            throw new \InvalidArgumentException('Candy type cannot be empty');
        }
    }

    private function validateItemQuantity(int $itemQuantity): void
    {
        if ($itemQuantity <= 0) {
            throw new \InvalidArgumentException('Item quantity must be greater than 0');
        }
    }

    private function validatePaidAmount(float $paidAmount): void
    {
        if ($paidAmount < 0) {
            throw new \InvalidArgumentException('Paid amount cannot be negative');
        }
    }
}
