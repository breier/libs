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

use ArrayIterator;
use ArrayObject;
use SplFixedArray;

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
    protected $arrayIterator;
    protected $arrayObject;
    protected $splFixedArray;

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
        $this->arrayIterator = new ArrayIterator($this->plainArray);
        $this->arrayObject = new ArrayObject($this->plainArray);
        $this->splFixedArray = new SplFixedArray(count($this->plainArray));

        $index = 0;
        foreach ($this->plainArray as $value) {
            $this->splFixedArray->offsetSet($index++, $value);
        }
    }

    /**
     * Test array works with every array type
     *
     * @return null
     * @test   array works with every array type
     */
    public function worksWithAllArrayTypes(): void
    {
        /**
         * Instantiated with Array
         */
        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->getArrayCopy()
        );

        /**
         * Instantiated with JSON string
         */
        $fromJSON = json_encode($this->plainArray);
        $this->assertSame(
            $this->plainArray,
            ExtendedArray::fromJSON($fromJSON)->getArrayCopy()
        );

        /**
         * Instantiated with ArrayIterator(array)
         */
        $this->assertSame(
            $this->plainArray,
            (new ExtendedArray($this->arrayIterator))->getArrayCopy()
        );

        /**
         * Instantiated with ArrayObject(array)
         */
        $this->assertSame(
            $this->plainArray,
            (new ExtendedArray($this->arrayObject))->getArrayCopy()
        );

        /**
         * Instantiated empty, then populated by unserialize
         */
        $fromSerialized = new ExtendedArray();
        $fromSerialized->unserialize(
            $this->extendedArray->serialize()
        );
        $this->assertSame(
            $this->plainArray,
            $fromSerialized->getArrayCopy()
        );

        /**
         * Instantiated with SplFixedArray
         */
        $nonAssocArray = $this->splFixedArray->toArray();
        $this->assertSame(
            $nonAssocArray,
            (new ExtendedArray($nonAssocArray))->getArrayCopy()
        );
    }

    /**
     * Test throw InvalidArgumentException Exception for non-arrays
     *
     * @return null
     * @test   throw InvalidArgumentException Exception for non-arrays
     */
    public function throwsForNonArrays(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $test = new ExtendedArray('not an array');
    }

    /**
     * Test returned the correct array as JSON
     *
     * @return null
     * @test   returned the correct array as JSON
     */
    public function returnedArrayAsJSON(): void
    {
        $plainArrayJSON = json_encode($this->plainArray);
        $extendedArrayJSON = $this->extendedArray->jsonSerialize();

        $this->assertSame($plainArrayJSON, $extendedArrayJSON);
    }

    /**
     * Test returned the correct sorted array
     *
     * @return null
     * @test   returned the correct sorted array
     */
    public function returnsCorrectSortedArray(): void
    {
        /**
         * Sort by value ascending
         */
        asort($this->plainArray);
        $this->extendedArray->asort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );

        /**
         * Sort by value descending
         */
        arsort($this->plainArray);
        $this->extendedArray->arsort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );

        /**
         * Sort by key ascending
         */
        ksort($this->plainArray);
        $this->extendedArray->ksort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );

        /**
         * Sort by key descending
         */
        krsort($this->plainArray);
        $this->extendedArray->krsort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
    }

    /**
     * Test Is Array works with all array types
     *
     * @return null
     * @test   Is Array works with all array types
     */
    public function isArrayWorksWithAllArrayTypes(): void
    {
        $this->assertTrue(ExtendedArray::isArray($this->plainArray));
        $this->assertTrue(ExtendedArray::isArray($this->extendedArray));
        $this->assertTrue(ExtendedArray::isArray($this->arrayIterator));
        $this->assertTrue(ExtendedArray::isArray($this->arrayObject));
        $this->assertTrue(ExtendedArray::isArray($this->splFixedArray));

        $this->assertFalse(ExtendedArray::isArray(null));
        $this->assertFalse(ExtendedArray::isArray(false));
        $this->assertFalse(ExtendedArray::isArray($this));
        $this->assertFalse(ExtendedArray::isArray(1024));
        $this->assertFalse(
            ExtendedArray::isArray($this->extendedArray->serialize())
        );
        $this->assertFalse(
            ExtendedArray::isArray($this->extendedArray->jsonSerialize())
        );
    }

    /**
     * Test 1000 ElementArray takes less than 50ms
     *
     * @return null
     * @test   1000 ElementArray takes less than 50ms
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
