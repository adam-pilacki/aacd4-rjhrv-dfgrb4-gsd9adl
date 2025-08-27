<?php

namespace Tests\Machine;

use App\Machine\CandyCatalog;
use App\Machine\CandyMachine;
use App\Machine\PurchaseTransaction;
use PHPUnit\Framework\TestCase;

class InputValidationTest extends TestCase
{
    private CandyMachine $machine;
    private CandyCatalog $catalog;

    protected function setUp(): void
    {
        $this->catalog = new CandyCatalog();
        $this->machine = new CandyMachine($this->catalog);
    }

    public function testValidCandyTypes(): void
    {
        $validTypes = ['caramels', 'lollipop', 'mince drops', 'chewing gum', 'licorice'];
        
        foreach ($validTypes as $type) {
            $transaction = new PurchaseTransaction($type, 1, 10.00);
            $result = $this->machine->execute($transaction);
            
            $this->assertEquals($type, $result->getType());
        }
    }

    public function testInvalidCandyTypes(): void
    {
        $invalidTypes = [
            'invalid_candy',
            'caramel', // similar but incorrect
            'LOLLIPOP', // uppercase
            'lollipop ', // space at the end
            ' chewing gum ', // spaces at beginning and end
            'mince-drops', // dash instead of space
            'chewing_gum', // underscore instead of space
            'licorice123', // digits at the end
            '123caramels', // digits at the beginning
            'caramels@', // special characters
            'caramels#123', // special characters and digits
        ];

        foreach ($invalidTypes as $type) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage("Invalid candy type: {$type}");
            
            $transaction = new PurchaseTransaction($type, 1, 10.00);
            $this->machine->execute($transaction);
        }
    }

    public function testValidQuantities(): void
    {
        $validQuantities = [1, 2, 5, 10, 100, 1000, 9999];
        
        foreach ($validQuantities as $quantity) {
            $transaction = new PurchaseTransaction('mince drops', $quantity, 10000.00); // Increase amount
            $result = $this->machine->execute($transaction);
            
            $this->assertEquals($quantity, $result->getItemQuantity());
        }
    }

    public function testInvalidQuantities(): void
    {
        $invalidQuantities = [0, -1, -5, -100, -9999];
        
        foreach ($invalidQuantities as $quantity) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Item quantity must be greater than 0');
            
            new PurchaseTransaction('caramels', $quantity, 10.00);
        }
    }

    public function testValidPaymentAmounts(): void
    {
        $testCases = [
            ['candy' => 'caramels', 'quantity' => 1, 'payment' => 4.99], // exact amount
            ['candy' => 'caramels', 'quantity' => 1, 'payment' => 5.00], // overpayment
            ['candy' => 'caramels', 'quantity' => 1, 'payment' => 10.00], // large overpayment
            ['candy' => 'mince drops', 'quantity' => 1, 'payment' => 0.69], // exact amount
            ['candy' => 'mince drops', 'quantity' => 1, 'payment' => 1.00], // overpayment
            ['candy' => 'lollipop', 'quantity' => 5, 'payment' => 20.00], // multiple
        ];

        foreach ($testCases as $testCase) {
            $transaction = new PurchaseTransaction(
                $testCase['candy'],
                $testCase['quantity'],
                $testCase['payment']
            );
            
            $result = $this->machine->execute($transaction);
            
            $this->assertEquals($testCase['candy'], $result->getType());
            $this->assertEquals($testCase['quantity'], $result->getItemQuantity());
        }
    }

    public function testInvalidPaymentAmounts(): void
    {
        $testCases = [
            ['candy' => 'caramels', 'quantity' => 1, 'payment' => 4.98], // too little
            ['candy' => 'caramels', 'quantity' => 1, 'payment' => 4.50], // too little
            ['candy' => 'caramels', 'quantity' => 1, 'payment' => 0.00], // zero
            ['candy' => 'lollipop', 'quantity' => 2, 'payment' => 5.00], // too little (2 * 2.99 = 5.98)
            ['candy' => 'mince drops', 'quantity' => 10, 'payment' => 6.00], // too little (10 * 0.69 = 6.90)
        ];

        foreach ($testCases as $testCase) {
            $this->expectException(\App\Exception\InsufficientPaymentException::class);
            
            $transaction = new PurchaseTransaction(
                $testCase['candy'],
                $testCase['quantity'],
                $testCase['payment']
            );
            
            $this->machine->execute($transaction);
        }
    }

    public function testEdgeCaseQuantities(): void
    {
        // Test with a large quantity (but not too large to avoid rounding issues)
        $transaction = new PurchaseTransaction('mince drops', 1000, 1000.00);
        $result = $this->machine->execute($transaction);
        
        $this->assertEquals(1000, $result->getItemQuantity());
        $this->assertEquals(round(1000 * 0.69, 2), $result->getTotalAmount()); // 1000 * 0.69
    }

    public function testEdgeCasePaymentAmounts(): void
    {
        // Test with a very large payment amount
        $transaction = new PurchaseTransaction('caramels', 1, 999999.99);
        $result = $this->machine->execute($transaction);
        
        $this->assertEquals('caramels', $result->getType());
        $this->assertEquals(1, $result->getItemQuantity());
        $this->assertEquals(4.99, $result->getTotalAmount());
        
        $change = $result->getChange();
        $totalChange = $this->calculateTotalChange($change);
        $this->assertEquals(round(999999.99 - 4.99, 2), $totalChange);
    }

    public function testMixedValidationScenarios(): void
    {
        // Test with different combinations of valid and invalid data
        $scenarios = [
            // Valid scenarios
            ['type' => 'caramels', 'quantity' => 1, 'payment' => 5.00, 'should_pass' => true],
            ['type' => 'lollipop', 'quantity' => 3, 'payment' => 10.00, 'should_pass' => true],
            ['type' => 'mince drops', 'quantity' => 100, 'payment' => 100.00, 'should_pass' => true],
            
            // Invalid scenarios
            ['type' => 'invalid_candy', 'quantity' => 1, 'payment' => 10.00, 'should_pass' => false, 'exception' => \InvalidArgumentException::class],
            ['type' => 'caramels', 'quantity' => 0, 'payment' => 10.00, 'should_pass' => false, 'exception' => \InvalidArgumentException::class],
            ['type' => 'caramels', 'quantity' => 1, 'payment' => 4.00, 'should_pass' => false, 'exception' => \App\Exception\InsufficientPaymentException::class],
        ];

        foreach ($scenarios as $scenario) {
            if ($scenario['should_pass']) {
                $transaction = new PurchaseTransaction(
                    $scenario['type'],
                    $scenario['quantity'],
                    $scenario['payment']
                );
                
                $result = $this->machine->execute($transaction);
                $this->assertEquals($scenario['type'], $result->getType());
                $this->assertEquals($scenario['quantity'], $result->getItemQuantity());
            } else {
                $this->expectException($scenario['exception']);
                
                $transaction = new PurchaseTransaction(
                    $scenario['type'],
                    $scenario['quantity'],
                    $scenario['payment']
                );
                
                $this->machine->execute($transaction);
            }
        }
    }

    public function testTypeValidationInTransaction(): void
    {
        // Test if PurchaseTransaction validates type before passing to the machine
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Candy type cannot be empty');
        
        new PurchaseTransaction('', 1, 10.00);
    }

    public function testQuantityValidationInTransaction(): void
    {
        // Test if PurchaseTransaction validates quantity
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Item quantity must be greater than 0');
        
        new PurchaseTransaction('caramels', -5, 10.00);
    }

    public function testPaymentValidationInTransaction(): void
    {
        // Test if PurchaseTransaction validates payment amount
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Paid amount cannot be negative');
        
        new PurchaseTransaction('caramels', 1, -10.00);
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
