<?php

namespace Tests\Exception;

use App\Exception\InsufficientPaymentException;
use PHPUnit\Framework\TestCase;

class InsufficientPaymentExceptionTest extends TestCase
{
    public function testExceptionCreationWithDefaultMessage(): void
    {
        $exception = new InsufficientPaymentException();
        
        $this->assertEquals('Insufficient payment amount', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionCreationWithCustomMessage(): void
    {
        $message = 'Custom insufficient payment message';
        $exception = new InsufficientPaymentException($message);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionCreationWithCustomMessageAndCode(): void
    {
        $message = 'Custom message';
        $code = 100;
        $exception = new InsufficientPaymentException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionCreationWithCustomMessageCodeAndPrevious(): void
    {
        $message = 'Custom message';
        $code = 200;
        $previous = new \Exception('Previous exception');
        $exception = new InsufficientPaymentException($message, $code, $previous);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionIsInstanceOfException(): void
    {
        $exception = new InsufficientPaymentException();
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testExceptionCanBeThrownAndCaught(): void
    {
        $this->expectException(InsufficientPaymentException::class);
        $this->expectExceptionMessage('Test message');
        
        throw new InsufficientPaymentException('Test message');
    }
}
