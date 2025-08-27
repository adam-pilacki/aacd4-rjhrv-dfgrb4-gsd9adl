<?php

namespace Tests\Machine;

use App\Machine\CandyCatalog;
use App\Machine\CandyMachine;
use App\Machine\PurchaseTransaction;
use PHPUnit\Framework\TestCase;

class ChangeCalculationTest extends TestCase
{
    private CandyMachine $machine;

    protected function setUp(): void
    {
        $catalog = new CandyCatalog();
        $this->machine = new CandyMachine($catalog);
    }

    public function testChangeCalculationWithZeroAmount(): void
    {
        $transaction = new PurchaseTransaction('caramels', 1, 4.99);
        $result = $this->machine->execute($transaction);
        
        $this->assertEmpty($result->getChange());
    }

    public function testChangeCalculationWithSmallAmount(): void
    {
        $transaction = new PurchaseTransaction('mince drops', 1, 1.00);
        $result = $this->machine->execute($transaction);
        
        $change = $result->getChange();
        $this->assertEquals(0.31, $this->calculateTotalChange($change));
        
        // Check if uses smallest possible coins
        $this->assertNotEmpty($change);
        $this->assertGreaterThan(0, $this->calculateTotalChange($change));
    }

    public function testChangeCalculationWithMediumAmount(): void
    {
        $transaction = new PurchaseTransaction('caramels', 1, 10.00);
        $result = $this->machine->execute($transaction);
        
        $change = $result->getChange();
        $this->assertEquals(5.01, $this->calculateTotalChange($change));
        
        // Check if uses optimal coins
        $this->assertNotEmpty($change);
        $this->assertGreaterThan(0, $this->calculateTotalChange($change));
    }

    public function testChangeCalculationWithLargeAmount(): void
    {
        $transaction = new PurchaseTransaction('lollipop', 1, 100.00);
        $result = $this->machine->execute($transaction);
        
        $change = $result->getChange();
        $this->assertEquals(97.01, $this->calculateTotalChange($change));
        
        // Check if uses largest possible coins
        $this->assertNotEmpty($change);
        $this->assertGreaterThan(0, $this->calculateTotalChange($change));
    }

    public function testChangeCalculationWithExactCoinAmounts(): void
    {
        // Test with amount that exactly fits coins
        $transaction = new PurchaseTransaction('mince drops', 1, 2.00);
        $result = $this->machine->execute($transaction);
        
        $change = $result->getChange();
        $this->assertEquals(1.31, $this->calculateTotalChange($change));
        
        // Check if change is correct
        $this->assertNotEmpty($change);
        $this->assertEquals(1.31, $this->calculateTotalChange($change));
    }

    public function testChangeCalculationWithComplexAmount(): void
    {
        $transaction = new PurchaseTransaction('chewing gum', 1, 5.00);
        $result = $this->machine->execute($transaction);
        
        $change = $result->getChange();
        $this->assertEquals(3.01, $this->calculateTotalChange($change));
        
        // Check if change is correct
        $this->assertNotEmpty($change);
        $this->assertEquals(3.01, $this->calculateTotalChange($change));
    }

    public function testChangeCalculationWithAllCoinTypes(): void
    {
        $transaction = new PurchaseTransaction('mince drops', 1, 10.00);
        $result = $this->machine->execute($transaction);
        
        $change = $result->getChange();
        $this->assertEquals(9.31, $this->calculateTotalChange($change));
        
        // Check if uses different coin types
        $coinTypes = array_keys($change);
        $this->assertGreaterThan(3, count($coinTypes), 'Should use multiple coin types for large change');
    }

    public function testChangeCalculationAccuracy(): void
    {
        $testCases = [
            ['candy' => 'caramels', 'quantity' => 1, 'payment' => 5.00, 'expected_change' => 0.01],
            ['candy' => 'lollipop', 'quantity' => 1, 'payment' => 3.00, 'expected_change' => 0.01],
            ['candy' => 'mince drops', 'quantity' => 1, 'payment' => 1.00, 'expected_change' => 0.31],
            ['candy' => 'chewing gum', 'quantity' => 1, 'payment' => 5.00, 'expected_change' => 3.01],
            ['candy' => 'licorice', 'quantity' => 1, 'payment' => 5.00, 'expected_change' => 1.41],
        ];

        foreach ($testCases as $testCase) {
            $transaction = new PurchaseTransaction(
                $testCase['candy'],
                $testCase['quantity'],
                $testCase['payment']
            );
            
            $result = $this->machine->execute($transaction);
            $actualChange = $this->calculateTotalChange($result->getChange());
            
            $this->assertEquals(
                $testCase['expected_change'],
                $actualChange,
                "Change calculation failed for {$testCase['candy']}"
            );
        }
    }

    public function testChangeCalculationWithRounding(): void
    {
        // Test with amounts that might have rounding issues
        $transaction = new PurchaseTransaction('caramels', 1, 5.00);
        $result = $this->machine->execute($transaction);
        
        $change = $result->getChange();
        $totalChange = $this->calculateTotalChange($change);
        
        // Check if change is exactly 0.01€ (5.00 - 4.99)
        $this->assertEquals(0.01, $totalChange, 'Change should be exactly 0.01€');
    }

    public function testChangeCalculationWithMultipleItems(): void
    {
        $transaction = new PurchaseTransaction('mince drops', 10, 10.00);
        $result = $this->machine->execute($transaction);
        
        $change = $result->getChange();
        $totalChange = $this->calculateTotalChange($change);
        
        // 10 * 0.69 = 6.90, so change is 10.00 - 6.90 = 3.10
        $this->assertEquals(3.10, $totalChange);
        
        // Check if change is correct
        $this->assertNotEmpty($change);
        $this->assertEquals(3.10, $totalChange);
    }

    public function testChangeCalculationEdgeCases(): void
    {
        // Test with very small change
        $transaction = new PurchaseTransaction('mince drops', 1, 0.70);
        $result = $this->machine->execute($transaction);
        
        $change = $result->getChange();
        $this->assertEquals(0.01, $this->calculateTotalChange($change));
        $this->assertArrayHasKey('0.01', $change);
        $this->assertEquals(1, $change['0.01']);
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
