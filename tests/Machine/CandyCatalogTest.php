<?php

namespace Tests\Machine;

use App\Machine\CandyCatalog;
use PHPUnit\Framework\TestCase;

class CandyCatalogTest extends TestCase
{
    private CandyCatalog $catalog;

    protected function setUp(): void
    {
        $this->catalog = new CandyCatalog();
    }

    public function testGetAvailableCandiesReturnsAllCandiesWithPrices(): void
    {
        $expected = [
            'caramels' => 4.99,
            'lollipop' => 2.99,
            'mince drops' => 0.69,
            'chewing gum' => 1.99,
            'licorice' => 3.59,
        ];

        $result = $this->catalog->getAvailableCandies();

        $this->assertEquals($expected, $result);
    }

    public function testGetCandyNamesReturnsAllCandyNames(): void
    {
        $expected = ['caramels', 'lollipop', 'mince drops', 'chewing gum', 'licorice'];

        $result = $this->catalog->getCandyNames();

        $this->assertEquals($expected, $result);
    }

    public function testGetCandyPriceReturnsCorrectPriceForValidCandy(): void
    {
        $this->assertEquals(4.99, $this->catalog->getCandyPrice('caramels'));
        $this->assertEquals(2.99, $this->catalog->getCandyPrice('lollipop'));
        $this->assertEquals(0.69, $this->catalog->getCandyPrice('mince drops'));
        $this->assertEquals(1.99, $this->catalog->getCandyPrice('chewing gum'));
        $this->assertEquals(3.59, $this->catalog->getCandyPrice('licorice'));
    }

    public function testGetCandyPriceThrowsExceptionForInvalidCandyType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid candy type: invalid_candy');

        $this->catalog->getCandyPrice('invalid_candy');
    }

    public function testGetCandyPriceThrowsExceptionForEmptyCandyType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid candy type: ');

        $this->catalog->getCandyPrice('');
    }

    public function testIsValidCandyTypeReturnsTrueForValidCandies(): void
    {
        $this->assertTrue($this->catalog->isValidCandyType('caramels'));
        $this->assertTrue($this->catalog->isValidCandyType('lollipop'));
        $this->assertTrue($this->catalog->isValidCandyType('mince drops'));
        $this->assertTrue($this->catalog->isValidCandyType('chewing gum'));
        $this->assertTrue($this->catalog->isValidCandyType('licorice'));
    }

    public function testIsValidCandyTypeReturnsFalseForInvalidCandies(): void
    {
        $this->assertFalse($this->catalog->isValidCandyType('invalid_candy'));
        $this->assertFalse($this->catalog->isValidCandyType(''));
        $this->assertFalse($this->catalog->isValidCandyType('CARAMELS'));
        $this->assertFalse($this->catalog->isValidCandyType('caramel'));
        $this->assertFalse($this->catalog->isValidCandyType('123'));
    }

    public function testCandyPricesAreCorrectlyFormatted(): void
    {
        $candies = $this->catalog->getAvailableCandies();
        
        foreach ($candies as $name => $price) {
            $this->assertIsFloat($price);
            $this->assertGreaterThan(0, $price);
            $this->assertLessThan(10, $price); // All prices are less than 10€
        }
    }

    public function testCandyNamesAreNonEmptyStrings(): void
    {
        $names = $this->catalog->getCandyNames();
        
        foreach ($names as $name) {
            $this->assertIsString($name);
            $this->assertNotEmpty(trim($name));
        }
    }
}
