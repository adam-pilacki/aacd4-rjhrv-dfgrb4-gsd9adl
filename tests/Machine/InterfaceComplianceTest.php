<?php

namespace Tests\Machine;

use App\Machine\CandyCatalog;
use App\Machine\CandyMachine;
use App\Machine\MachineInterface;
use App\Machine\PurchaseTransaction;
use App\Machine\PurchaseTransactionInterface;
use App\Machine\PurchasedItem;
use App\Machine\PurchasedItemInterface;
use PHPUnit\Framework\TestCase;

class InterfaceComplianceTest extends TestCase
{
    public function testCandyMachineImplementsMachineInterface(): void
    {
        $catalog = new CandyCatalog();
        $machine = new CandyMachine($catalog);
        
        $this->assertInstanceOf(MachineInterface::class, $machine);
    }

    public function testPurchaseTransactionImplementsPurchaseTransactionInterface(): void
    {
        $transaction = new PurchaseTransaction('caramels', 1, 5.00);
        
        $this->assertInstanceOf(PurchaseTransactionInterface::class, $transaction);
    }

    public function testPurchasedItemImplementsPurchasedItemInterface(): void
    {
        $item = new PurchasedItem('caramels', 1, 4.99, []);
        
        $this->assertInstanceOf(PurchasedItemInterface::class, $item);
    }

    public function testMachineInterfaceContract(): void
    {
        $catalog = new CandyCatalog();
        $machine = new CandyMachine($catalog);
        
        $transaction = new PurchaseTransaction('caramels', 1, 5.00);
        $result = $machine->execute($transaction);
        
        // Check if the result implements the required interface
        $this->assertInstanceOf(PurchasedItemInterface::class, $result);
        
        // Check if the result has all required methods
        $this->assertIsString($result->getType());
        $this->assertIsInt($result->getItemQuantity());
        $this->assertIsFloat($result->getTotalAmount());
        $this->assertIsArray($result->getChange());
    }

    public function testPurchaseTransactionInterfaceContract(): void
    {
        $transaction = new PurchaseTransaction('lollipop', 2, 10.00);
        
        // Check if the transaction has all required methods
        $this->assertIsString($transaction->getType());
        $this->assertIsInt($transaction->getItemQuantity());
        $this->assertIsFloat($transaction->getPaidAmount());
        
        // Check if values are correct
        $this->assertEquals('lollipop', $transaction->getType());
        $this->assertEquals(2, $transaction->getItemQuantity());
        $this->assertEquals(10.00, $transaction->getPaidAmount());
    }

    public function testPurchasedItemInterfaceContract(): void
    {
        $change = ['0.50' => 1, '0.02' => 1];
        $item = new PurchasedItem('mince drops', 3, 2.07, $change);
        
        // Check if the item has all required methods
        $this->assertIsString($item->getType());
        $this->assertIsInt($item->getItemQuantity());
        $this->assertIsFloat($item->getTotalAmount());
        $this->assertIsArray($item->getChange());
        
        // Check if values are correct
        $this->assertEquals('mince drops', $item->getType());
        $this->assertEquals(3, $item->getItemQuantity());
        $this->assertEquals(2.07, $item->getTotalAmount());
        $this->assertEquals($change, $item->getChange());
    }

    public function testMachineExecuteMethodSignature(): void
    {
        $catalog = new CandyCatalog();
        $machine = new CandyMachine($catalog);
        
        $reflection = new \ReflectionClass($machine);
        $executeMethod = $reflection->getMethod('execute');
        
        // Check the execute method signature
        $this->assertEquals('execute', $executeMethod->getName());
        $this->assertTrue($executeMethod->isPublic());
        
        $parameters = $executeMethod->getParameters();
        $this->assertCount(1, $parameters);
        
        $parameter = $parameters[0];
        $this->assertEquals('purchaseTransaction', $parameter->getName());
        $this->assertTrue($parameter->getType()->getName() === 'App\Machine\PurchaseTransactionInterface');
        
        $returnType = $executeMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('App\Machine\PurchasedItemInterface', $returnType->getName());
    }

    public function testPurchaseTransactionMethodSignatures(): void
    {
        $reflection = new \ReflectionClass(PurchaseTransaction::class);
        
        $getTypeMethod = $reflection->getMethod('getType');
        $getItemQuantityMethod = $reflection->getMethod('getItemQuantity');
        $getPaidAmountMethod = $reflection->getMethod('getPaidAmount');
        
        // Check method signatures
        $this->assertTrue($getTypeMethod->isPublic());
        $this->assertTrue($getItemQuantityMethod->isPublic());
        $this->assertTrue($getPaidAmountMethod->isPublic());
        
        // Check return types
        $this->assertEquals('string', $getTypeMethod->getReturnType()->getName());
        $this->assertEquals('int', $getItemQuantityMethod->getReturnType()->getName());
        $this->assertEquals('float', $getPaidAmountMethod->getReturnType()->getName());
    }

    public function testPurchasedItemMethodSignatures(): void
    {
        $reflection = new \ReflectionClass(PurchasedItem::class);
        
        $getTypeMethod = $reflection->getMethod('getType');
        $getItemQuantityMethod = $reflection->getMethod('getItemQuantity');
        $getTotalAmountMethod = $reflection->getMethod('getTotalAmount');
        $getChangeMethod = $reflection->getMethod('getChange');
        $getUnitPriceMethod = $reflection->getMethod('getUnitPrice');
        
        // Check method signatures
        $this->assertTrue($getTypeMethod->isPublic());
        $this->assertTrue($getItemQuantityMethod->isPublic());
        $this->assertTrue($getTotalAmountMethod->isPublic());
        $this->assertTrue($getChangeMethod->isPublic());
        $this->assertTrue($getUnitPriceMethod->isPublic());
        
        // Check return types
        $this->assertEquals('string', $getTypeMethod->getReturnType()->getName());
        $this->assertEquals('int', $getItemQuantityMethod->getReturnType()->getName());
        $this->assertEquals('float', $getTotalAmountMethod->getReturnType()->getName());
        $this->assertEquals('array', $getChangeMethod->getReturnType()->getName());
        $this->assertEquals('float', $getUnitPriceMethod->getReturnType()->getName());
    }

    public function testInterfaceMethodReturnTypes(): void
    {
        // Test MachineInterface
        $catalog = new CandyCatalog();
        $machine = new CandyMachine($catalog);
        
        $transaction = new PurchaseTransaction('caramels', 1, 5.00);
        $result = $machine->execute($transaction);
        
        $this->assertInstanceOf(PurchasedItemInterface::class, $result);
        
        // Test PurchaseTransactionInterface
        $this->assertInstanceOf(PurchaseTransactionInterface::class, $transaction);
        
        // Test PurchasedItemInterface
        $this->assertInstanceOf(PurchasedItemInterface::class, $result);
    }

    public function testInterfaceMethodBehavior(): void
    {
        $catalog = new CandyCatalog();
        $machine = new CandyMachine($catalog);
        
        $transaction = new PurchaseTransaction('licorice', 2, 10.00);
        $result = $machine->execute($transaction);
        
        // Check if all interface methods work correctly
        $this->assertEquals('licorice', $result->getType());
        $this->assertEquals(2, $result->getItemQuantity());
        $this->assertEquals(7.18, $result->getTotalAmount()); // 2 * 3.59
        $this->assertIsArray($result->getChange());
        
        // Check if getUnitPrice works correctly
        $this->assertEquals(3.59, $result->getUnitPrice());
    }

    public function testInterfaceImmutability(): void
    {
        $catalog = new CandyCatalog();
        $machine = new CandyMachine($catalog);
        
        $transaction = new PurchaseTransaction('chewing gum', 1, 5.00);
        $result1 = $machine->execute($transaction);
        $result2 = $machine->execute($transaction);
        
        // Check if results are identical (immutability)
        $this->assertEquals($result1->getType(), $result2->getType());
        $this->assertEquals($result1->getItemQuantity(), $result2->getItemQuantity());
        $this->assertEquals($result1->getTotalAmount(), $result2->getTotalAmount());
        $this->assertEquals($result1->getChange(), $result2->getChange());
    }
}
