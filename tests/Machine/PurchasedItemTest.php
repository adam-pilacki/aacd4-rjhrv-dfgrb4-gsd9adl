<?php

namespace Tests\Machine;

use App\Machine\PurchasedItem;
use PHPUnit\Framework\TestCase;

class PurchasedItemTest extends TestCase
{
    public function testValidPurchasedItemCreation(): void
    {
        $change = ['0.50' => 1, '0.02' => 1];
        $item = new PurchasedItem('caramels', 2, 9.98, $change);

        $this->assertEquals('caramels', $item->getType());
        $this->assertEquals(2, $item->getItemQuantity());
        $this->assertEquals(9.98, $item->getTotalAmount());
        $this->assertEquals($change, $item->getChange());
    }

    public function testGetTypeReturnsCorrectType(): void
    {
        $item = new PurchasedItem('lollipop', 1, 2.99, []);
        
        $this->assertEquals('lollipop', $item->getType());
    }

    public function testGetItemQuantityReturnsCorrectQuantity(): void
    {
        $item = new PurchasedItem('mince drops', 5, 3.45, []);
        
        $this->assertEquals(5, $item->getItemQuantity());
    }

    public function testGetTotalAmountReturnsCorrectAmount(): void
    {
        $item = new PurchasedItem('chewing gum', 3, 5.97, []);
        
        $this->assertEquals(5.97, $item->getTotalAmount());
    }

    public function testGetChangeReturnsCorrectChange(): void
    {
        $change = ['1.00' => 2, '0.50' => 1, '0.01' => 3];
        $item = new PurchasedItem('licorice', 1, 3.59, $change);
        
        $this->assertEquals($change, $item->getChange());
    }

    public function testGetUnitPriceCalculatesCorrectPrice(): void
    {
        $item = new PurchasedItem('caramels', 2, 9.98, []);
        
        $this->assertEquals(4.99, $item->getUnitPrice());
    }

    public function testGetUnitPriceWithSingleItem(): void
    {
        $item = new PurchasedItem('lollipop', 1, 2.99, []);
        
        $this->assertEquals(2.99, $item->getUnitPrice());
    }

    public function testGetUnitPriceWithMultipleItems(): void
    {
        $item = new PurchasedItem('mince drops', 10, 6.90, []);
        
        $this->assertEquals(round(6.90 / 10, 2), $item->getUnitPrice());
    }

    public function testGetUnitPriceWithDecimalResult(): void
    {
        $item = new PurchasedItem('chewing gum', 3, 5.97, []);
        
        $this->assertEquals(1.99, $item->getUnitPrice());
    }

    public function testEmptyChangeArray(): void
    {
        $item = new PurchasedItem('caramels', 1, 4.99, []);
        
        $this->assertEmpty($item->getChange());
        $this->assertIsArray($item->getChange());
    }

    public function testComplexChangeArray(): void
    {
        $change = [
            '2.00' => 1,
            '1.00' => 2,
            '0.50' => 1,
            '0.20' => 2,
            '0.10' => 1,
            '0.05' => 1,
            '0.02' => 2,
            '0.01' => 1
        ];
        
        $item = new PurchasedItem('licorice', 1, 3.59, $change);
        
        $this->assertEquals($change, $item->getChange());
        $this->assertCount(8, $item->getChange());
    }

    public function testChangeWithZeroCounts(): void
    {
        $change = ['1.00' => 0, '0.50' => 1, '0.20' => 0];
        $item = new PurchasedItem('caramels', 1, 4.99, $change);
        
        $this->assertEquals($change, $item->getChange());
    }

    public function testLargeQuantities(): void
    {
        $item = new PurchasedItem('mince drops', 1000, 690.00, []);
        
        $this->assertEquals(1000, $item->getItemQuantity());
        $this->assertEquals(690.00, $item->getTotalAmount());
        $this->assertEquals(0.69, $item->getUnitPrice());
    }

    public function testLargeAmounts(): void
    {
        $item = new PurchasedItem('caramels', 1, 999999.99, []);
        
        $this->assertEquals(999999.99, $item->getTotalAmount());
        $this->assertEquals(999999.99, $item->getUnitPrice());
    }

    public function testChangeWithStringKeys(): void
    {
        $change = ['0.50' => 1, '0.02' => 1];
        $item = new PurchasedItem('lollipop', 1, 2.99, $change);
        
        $this->assertEquals($change, $item->getChange());
        $this->assertArrayHasKey('0.50', $item->getChange());
        $this->assertArrayHasKey('0.02', $item->getChange());
    }

    public function testChangeWithNumericKeys(): void
    {
        $change = [0.50 => 1, 0.02 => 1];
        $item = new PurchasedItem('chewing gum', 1, 1.99, $change);
        
        $this->assertEquals($change, $item->getChange());
    }

    public function testImmutability(): void
    {
        $originalChange = ['0.50' => 1];
        $item = new PurchasedItem('licorice', 1, 3.59, $originalChange);
        
        $change = $item->getChange();
        $change['0.50'] = 5; // Modify copy
        
        $this->assertEquals($originalChange, $item->getChange()); // Original unchanged
    }
}
