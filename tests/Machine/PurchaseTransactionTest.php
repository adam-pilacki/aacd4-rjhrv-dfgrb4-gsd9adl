<?php

namespace Tests\Machine;

use App\Machine\PurchaseTransaction;
use PHPUnit\Framework\TestCase;

class PurchaseTransactionTest extends TestCase
{
    public function testValidPurchaseTransactionCreation(): void
    {
        $transaction = new PurchaseTransaction('caramels', 2, 10.00);

        $this->assertEquals('caramels', $transaction->getType());
        $this->assertEquals(2, $transaction->getItemQuantity());
        $this->assertEquals(10.00, $transaction->getPaidAmount());
    }

    public function testGetTypeReturnsCorrectType(): void
    {
        $transaction = new PurchaseTransaction('lollipop', 1, 5.00);
        
        $this->assertEquals('lollipop', $transaction->getType());
    }

    public function testGetItemQuantityReturnsCorrectQuantity(): void
    {
        $transaction = new PurchaseTransaction('mince drops', 5, 10.00);
        
        $this->assertEquals(5, $transaction->getItemQuantity());
    }

    public function testGetPaidAmountReturnsCorrectAmount(): void
    {
        $transaction = new PurchaseTransaction('chewing gum', 3, 15.50);
        
        $this->assertEquals(15.50, $transaction->getPaidAmount());
    }

    public function testTransactionWithZeroQuantityThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Item quantity must be greater than 0');

        new PurchaseTransaction('caramels', 0, 10.00);
    }

    public function testTransactionWithNegativeQuantityThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Item quantity must be greater than 0');

        new PurchaseTransaction('caramels', -1, 10.00);
    }

    public function testTransactionWithEmptyTypeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Candy type cannot be empty');

        new PurchaseTransaction('', 1, 10.00);
    }

    public function testTransactionWithWhitespaceOnlyTypeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Candy type cannot be empty');

        new PurchaseTransaction('   ', 1, 10.00);
    }

    public function testTransactionWithNegativePaymentAmountThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Paid amount cannot be negative');

        new PurchaseTransaction('caramels', 1, -5.00);
    }

    public function testTransactionWithZeroPaymentAmountIsValid(): void
    {
        $transaction = new PurchaseTransaction('caramels', 1, 0.00);
        
        $this->assertEquals(0.00, $transaction->getPaidAmount());
    }

    public function testTransactionWithLargeQuantityIsValid(): void
    {
        $transaction = new PurchaseTransaction('mince drops', 1000, 1000.00);
        
        $this->assertEquals(1000, $transaction->getItemQuantity());
        $this->assertEquals(1000.00, $transaction->getPaidAmount());
    }

    public function testTransactionWithLargePaymentAmountIsValid(): void
    {
        $transaction = new PurchaseTransaction('licorice', 1, 999999.99);
        
        $this->assertEquals(999999.99, $transaction->getPaidAmount());
    }
}
