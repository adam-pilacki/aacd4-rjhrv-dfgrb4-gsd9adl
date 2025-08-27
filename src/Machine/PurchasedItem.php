<?php

namespace App\Machine;

final class PurchasedItem implements PurchasedItemInterface
{
    private string $type;
    private int $itemQuantity;
    private float $totalAmount;
    private array $change;

    public function __construct(string $type, int $itemQuantity, float $totalAmount, array $change)
    {
        $this->type = $type;
        $this->itemQuantity = $itemQuantity;
        $this->totalAmount = $totalAmount;
        $this->change = $change;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getItemQuantity(): int
    {
        return $this->itemQuantity;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getChange(): array
    {
        return $this->change;
    }

    public function getUnitPrice(): float
    {
        return round($this->totalAmount / $this->itemQuantity, 2);
    }
}
