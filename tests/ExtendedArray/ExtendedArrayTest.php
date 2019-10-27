<?php
/**
 * Class ExtendedArrayTest
 *
 * PHP version 7
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/phpunit/phpunit/phpunit tests/ExtendedArrayTest.php
 */

namespace Test\ExtendedArray;

use Breier\ExtendedArray\ExtendedArray;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use TypeError;

/**
 * Class ExtendedArrayTest
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/phpunit/phpunit/phpunit tests/ExtendedArrayTest.php
 */
class ExtendedArrayTest extends TestCase
{
    protected $plainArray;
    protected $extendedArray;

    /**
     * Set up an example array for every test
     *
     * @return null
     */
    public function setUp(): void
    {
        $this->plainArray = [
            'one' => 1,
            [2 => 'two', 'three'],
            7 => 'four',
            'five'
        ];

        $this->extendedArray = new ExtendedArray($this->plainArray);
    }

    /**
     * Test array can be reverted with same values
     *
     * @test   array can be reverted with same values
     * @return null
     */
    public function constantsArrayCanBeReverted(): void
    {
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
    }

    /**
     * Test throw InvalidArgumentException Exception for non-arrays
     *
     * @test   throw InvalidArgumentException Exception for non-arrays
     * @return null
     */
    public function throwsForNonArrays(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $test = new ExtendedArray('not an array');
    }

    /**
     * Test returned the correct array as JSON
     *
     * @test   returned the correct array as JSON
     * @return null
     */
    public function returnedArrayIsJSON(): void
    {
        $plainArrayJSON = json_encode($this->plainArray);
        $extendedArrayJSON = $this->extendedArray->jsonSerialize();

        //$this->assertTrue(is_array($incompleteArray));
        $this->assertSame($plainArrayJSON, $extendedArrayJSON);
    }

    /**
     * Test returned the correct sorted array
     *
     * @test   returned the correct sorted array
     * @return null
     */
    public function returnsCorrectSortedArray(): void
    {
        asort($this->plainArray);
        $this->extendedArray->asort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());

        arsort($this->plainArray);
        $this->extendedArray->arsort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());

        ksort($this->plainArray);
        $this->extendedArray->ksort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());

        krsort($this->plainArray);
        $this->extendedArray->krsort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
    }

    /**
     * Test 1000 ElementArray takes less than 50ms
     *
     * @test   1000 ElementArray takes less than 50ms
     * @return null
     */
    public function executionTimeIsAcceptable(): void
    {
        $startTime = microtime(true);

        $this->extendedArray->map(
            function ($item) {
                return is_string($item);
            }
        )->filter();

        $timeTaken = microtime(true) - $startTime;

        $this->assertLessThan(0.05, $timeTaken);
    }
}
