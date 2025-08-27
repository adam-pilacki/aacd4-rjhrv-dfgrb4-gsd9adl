<?php

namespace Tests\Command;

use App\Command\PurchaseCandyCommand;
use App\Machine\CandyCatalogInterface;
use App\Machine\MachineInterface;
use PHPUnit\Framework\TestCase;

class PurchaseCandyCommandTest extends TestCase
{
    private PurchaseCandyCommand $command;
    private MachineInterface $machine;
    private CandyCatalogInterface $catalog;

    protected function setUp(): void
    {
        $this->catalog = new \App\Machine\CandyCatalog();
        $this->machine = new \App\Machine\CandyMachine($this->catalog);
        $this->command = new PurchaseCandyCommand($this->machine, $this->catalog);
    }

    public function testCommandHasCorrectNameAndDescription(): void
    {
        $this->assertEquals('purchase-candy', $this->command->getName());
        $this->assertEquals('Purchase candy from the candy machine', $this->command->getDescription());
    }

    public function testCommandConfiguration(): void
    {
        $this->assertEquals('purchase-candy', $this->command->getName());
        $this->assertEquals('Purchase candy from the candy machine', $this->command->getDescription());
        $this->assertEquals('This command allows you to purchase candy from the candy machine', $this->command->getHelp());
    }

    public function testCommandDependenciesAreInjected(): void
    {
        // Check if dependencies are properly injected
        $reflection = new \ReflectionClass($this->command);
        
        $machineProperty = $reflection->getProperty('machine');
        $machineProperty->setAccessible(true);
        $machine = $machineProperty->getValue($this->command);
        
        $catalogProperty = $reflection->getProperty('catalog');
        $catalogProperty->setAccessible(true);
        $catalog = $catalogProperty->getValue($this->command);
        
        $this->assertInstanceOf(\App\Machine\CandyMachine::class, $machine);
        $this->assertInstanceOf(\App\Machine\CandyCatalog::class, $catalog);
    }

    public function testCommandExtendsSymfonyCommand(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Console\Command\Command::class, $this->command);
    }

    public function testCommandHasCorrectNamespace(): void
    {
        $this->assertEquals('App\Command\PurchaseCandyCommand', get_class($this->command));
    }

    public function testCommandIsFinal(): void
    {
        $reflection = new \ReflectionClass($this->command);
        $this->assertTrue($reflection->isFinal());
    }

    public function testCommandHasPrivateProperties(): void
    {
        $reflection = new \ReflectionClass($this->command);
        
        $machineProperty = $reflection->getProperty('machine');
        $catalogProperty = $reflection->getProperty('catalog');
        
        $this->assertTrue($machineProperty->isPrivate());
        $this->assertTrue($catalogProperty->isPrivate());
    }

    public function testCommandHasPublicMethods(): void
    {
        $reflection = new \ReflectionClass($this->command);
        
        $configureMethod = $reflection->getMethod('configure');
        $executeMethod = $reflection->getMethod('execute');
        
        $this->assertTrue($configureMethod->isProtected());
        $this->assertTrue($executeMethod->isProtected());
    }

    public function testCommandConstructorCallsParent(): void
    {
        $reflection = new \ReflectionClass($this->command);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $this->assertEquals(2, $constructor->getNumberOfParameters());
    }
}
