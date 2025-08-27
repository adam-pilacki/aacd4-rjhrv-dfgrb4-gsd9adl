<?php

namespace Tests\Machine;

use App\Exception\InsufficientPaymentException;
use App\Machine\CandyCatalog;
use App\Machine\CandyMachine;
use App\Machine\PurchaseTransaction;
use PHPUnit\Framework\TestCase;

class CandyMachineTest extends TestCase
{
    private CandyMachine $machine;
    private CandyCatalog $catalog;

    protected function setUp(): void
    {
        $this->catalog = new CandyCatalog();
        $this->machine = new CandyMachine($this->catalog);
    }

    public function testValidPurchaseTransaction(): void
    {
        $transaction = new PurchaseTransaction('caramels', 2, 10.00);
        
        $result = $this->machine->execute($transaction);
        
        $this->assertEquals('caramels', $result->getType());
        $this->assertEquals(2, $result->getItemQuantity());
        $this->assertEquals(9.98, $result->getTotalAmount());
        $this->assertEquals(4.99, $result->getUnitPrice());
        $this->assertEquals(['0.02' => 1], $result->getChange());
    }

    public function testPurchaseWithExactPayment(): void
    {
        $transaction = new PurchaseTransaction('lollipop', 1, 2.99);
        
        $result = $this->machine->execute($transaction);
        
        $this->assertEquals('lollipop', $result->getType());
        $this->assertEquals(1, $result->getItemQuantity());
        $this->assertEquals(2.99, $result->getTotalAmount());
        $this->assertEmpty($result->getChange());
    }

    public function testPurchaseWithLargeChange(): void
    {
        $transaction = new PurchaseTransaction('mince drops', 1, 10.00);
        
        $result = $this->machine->execute($transaction);
        
        $this->assertEquals('mince drops', $result->getType());
        $this->assertEquals(1, $result->getItemQuantity());
        $this->assertEquals(0.69, $result->getTotalAmount());
        
        $change = $result->getChange();
        $this->assertNotEmpty($change);
        $this->assertEquals(9.31, $this->calculateTotalChange($change));
    }

    public function testPurchaseMultipleItems(): void
    {
        $transaction = new PurchaseTransaction('chewing gum', 5, 20.00);
        
        $result = $this->machine->execute($transaction);
        
        $this->assertEquals('chewing gum', $result->getType());
        $this->assertEquals(5, $result->getItemQuantity());
        $this->assertEquals(round(1.99 * 5, 2), $result->getTotalAmount());
        $this->assertEquals(1.99, $result->getUnitPrice());
        
        $change = $result->getChange();
        $this->assertEquals(round(20.00 - (1.99 * 5), 2), $this->calculateTotalChange($change));
    }

    public function testInsufficientPaymentThrowsException(): void
    {
        $transaction = new PurchaseTransaction('licorice', 1, 3.00);
        
        $this->expectException(InsufficientPaymentException::class);
        $this->expectExceptionMessage('Insufficient payment. Required: 3.59€, Provided: 3€');
        
        $this->machine->execute($transaction);
    }

    public function testInsufficientPaymentForMultipleItems(): void
    {
        $transaction = new PurchaseTransaction('caramels', 3, 10.00);
        
        $this->expectException(InsufficientPaymentException::class);
        $this->expectExceptionMessage('Insufficient payment. Required: 14.97€, Provided: 10€');
        
        $this->machine->execute($transaction);
    }

    public function testInvalidCandyTypeThrowsException(): void
    {
        $transaction = new PurchaseTransaction('invalid_candy', 1, 10.00);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid candy type: invalid_candy');
        
        $this->machine->execute($transaction);
    }

    public function testChangeCalculationWithSmallAmount(): void
    {
        $transaction = new PurchaseTransaction('mince drops', 1, 1.00);
        
        $result = $this->machine->execute($transaction);
        
        $this->assertEquals(0.31, $this->calculateTotalChange($result->getChange()));
    }

    public function testChangeCalculationWithMediumAmount(): void
    {
        $transaction = new PurchaseTransaction('caramels', 1, 10.00);
        
        $result = $this->machine->execute($transaction);
        
        $this->assertEquals(5.01, $this->calculateTotalChange($result->getChange()));
    }

    public function testChangeCalculationWithLargeAmount(): void
    {
        $transaction = new PurchaseTransaction('lollipop', 1, 100.00);
        
        $result = $this->machine->execute($transaction);
        
        $this->assertEquals(97.01, $this->calculateTotalChange($result->getChange()));
    }

    public function testChangeCalculationWithComplexAmount(): void
    {
        $transaction = new PurchaseTransaction('chewing gum', 1, 5.00);
        
        $result = $this->machine->execute($transaction);
        
        $this->assertEquals(3.01, $this->calculateTotalChange($result->getChange()));
    }

    public function testAllCandyTypesWorkCorrectly(): void
    {
        $candyTypes = ['caramels', 'lollipop', 'mince drops', 'chewing gum', 'licorice'];
        
        foreach ($candyTypes as $candyType) {
            $price = $this->catalog->getCandyPrice($candyType);
            $transaction = new PurchaseTransaction($candyType, 1, $price + 1);
            
            $result = $this->machine->execute($transaction);
            
            $this->assertEquals($candyType, $result->getType());
            $this->assertEquals(1, $result->getItemQuantity());
            $this->assertEquals($price, $result->getTotalAmount());
            $this->assertEquals(1.00, $this->calculateTotalChange($result->getChange()));
        }
    }

    public function testLargeQuantityPurchase(): void
    {
        $transaction = new PurchaseTransaction('mince drops', 1000, 1000.00);
        
        $result = $this->machine->execute($transaction);
        
        $this->assertEquals('mince drops', $result->getType());
        $this->assertEquals(1000, $result->getItemQuantity());
        $this->assertEquals(690.00, $result->getTotalAmount());
        $this->assertEquals(0.69, $result->getUnitPrice());
        $this->assertEquals(310.00, $this->calculateTotalChange($result->getChange()));
    }

    public function testChangeCalculationAccuracy(): void
    {
        $transaction = new PurchaseTransaction('caramels', 1, 5.00);
        
        $result = $this->machine->execute($transaction);
        
        $change = $result->getChange();
        $totalChange = $this->calculateTotalChange($change);
        
        // Check if change is exactly 0.01€
        $this->assertEquals(0.01, $totalChange, 'Change calculation should be accurate to 2 decimal places');
    }

    public function testChangeWithAllCoinTypes(): void
    {
        $transaction = new PurchaseTransaction('mince drops', 1, 10.00);
        
        $result = $this->machine->execute($transaction);
        
        $change = $result->getChange();
        $this->assertGreaterThan(0, count($change), 'Should have some change');
        
        // Check if all coins are in available denominations
        $availableCoins = [2.00, 1.00, 0.50, 0.20, 0.10, 0.05, 0.02, 0.01];
        foreach (array_keys($change) as $coin) {
            $this->assertContains((float) $coin, $availableCoins, "Coin {$coin} should be in available coins");
        }
    }

    private function calculateTotalChange(array $change): float
    {
        $total = 0.0;
        foreach ($change as $coin => $count) {
            $total += (float) $coin * $count;
        }
        return round($total, 2);
    }
}
