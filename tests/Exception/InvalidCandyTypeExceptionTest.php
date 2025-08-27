<?php

namespace Tests\Exception;

use App\Exception\InvalidCandyTypeException;
use PHPUnit\Framework\TestCase;

class InvalidCandyTypeExceptionTest extends TestCase
{
    public function testExceptionCreationWithCandyType(): void
    {
        $candyType = 'invalid_candy';
        $exception = new InvalidCandyTypeException($candyType);
        
        $this->assertEquals("Invalid candy type: {$candyType}", $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionCreationWithEmptyCandyType(): void
    {
        $candyType = '';
        $exception = new InvalidCandyTypeException($candyType);
        
        $this->assertEquals("Invalid candy type: {$candyType}", $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionCreationWithWhitespaceCandyType(): void
    {
        $candyType = '   ';
        $exception = new InvalidCandyTypeException($candyType);
        
        $this->assertEquals("Invalid candy type: {$candyType}", $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionCreationWithSpecialCharactersCandyType(): void
    {
        $candyType = 'candy@#$%';
        $exception = new InvalidCandyTypeException($candyType);
        
        $this->assertEquals("Invalid candy type: {$candyType}", $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionCreationWithNumericCandyType(): void
    {
        $candyType = '123';
        $exception = new InvalidCandyTypeException($candyType);
        
        $this->assertEquals("Invalid candy type: {$candyType}", $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionCreationWithCustomCode(): void
    {
        $candyType = 'invalid_candy';
        $code = 500;
        $exception = new InvalidCandyTypeException($candyType, $code);
        
        $this->assertEquals("Invalid candy type: {$candyType}", $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionCreationWithCustomCodeAndPrevious(): void
    {
        $candyType = 'invalid_candy';
        $code = 600;
        $previous = new \Exception('Previous exception');
        $exception = new InvalidCandyTypeException($candyType, $code, $previous);
        
        $this->assertEquals("Invalid candy type: {$candyType}", $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionIsInstanceOfException(): void
    {
        $exception = new InvalidCandyTypeException('test');
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testExceptionCanBeThrownAndCaught(): void
    {
        $this->expectException(InvalidCandyTypeException::class);
        $this->expectExceptionMessage('Invalid candy type: test_candy');
        
        throw new InvalidCandyTypeException('test_candy');
    }

    public function testExceptionMessageFormatIsConsistent(): void
    {
        $testCases = [
            'simple' => 'simple',
            'with spaces' => 'with spaces',
            'UPPERCASE' => 'UPPERCASE',
            'mixed123' => 'mixed123',
            'special@#$' => 'special@#$',
            '' => '',
            '   ' => '   '
        ];

        foreach ($testCases as $input => $expected) {
            $exception = new InvalidCandyTypeException($input);
            $this->assertEquals("Invalid candy type: {$expected}", $exception->getMessage());
        }
    }
}
