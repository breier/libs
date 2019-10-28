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

use Breier\ExtendedArray\ExtendedArrayException;

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
        $this->assertSame(
            $this->splFixedArray->toArray(),
            (new ExtendedArray($this->splFixedArray))->getArrayCopy()
        );
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
     * Test cursor moves around as expected
     *
     * @return null
     * @test   cursor moves around as expected
     */
    public function cursorMovesAroundAsExpected(): void
    {
        $this->assertSame(key($this->plainArray), $this->extendedArray->key());

        end($this->plainArray);
        $this->extendedArray->last();
        $this->assertSame(key($this->plainArray), $this->extendedArray->key());

        reset($this->plainArray);
        $this->extendedArray->first();
        $this->assertSame(key($this->plainArray), $this->extendedArray->key());

        next($this->plainArray);
        $this->extendedArray->next();
        $this->assertSame(key($this->plainArray), $this->extendedArray->key());

        $this->assertSame(1, $this->extendedArray->pos());

        prev($this->plainArray);
        $this->extendedArray->prev();
        $this->assertSame(key($this->plainArray), $this->extendedArray->key());

        prev($this->plainArray);
        $this->extendedArray->prev();
        $this->assertSame(key($this->plainArray), $this->extendedArray->key());

        $this->extendedArray->seek(2);
        $this->assertSame(
            $this->plainArray[$this->extendedArray->key()],
            $this->extendedArray->element()
        );

        $this->seekKey($this->plainArray, 7);
        $this->extendedArray->seekKey(7);
        $this->assertSame(key($this->plainArray), $this->extendedArray->key());
    }

    /**
     * Test throw ExtendedArrayException for non-existent key
     *
     * @return null
     * @test   throw ExtendedArrayException for non-existent key
     */
    public function throwsForNonExistentKey(): void
    {
        $this->expectException(ExtendedArrayException::class);

        $this->extendedArray->seekKey('non-existent');
    }

    /**
     * Test Offset Get / First / Last / Position
     *
     * @return null
     * @test   Offset Get / First / Last / Position
     */
    public function offsetGetFirstLastPosition(): void
    {
        /**
         * OffsetGet string key
         */
        $this->assertSame($this->plainArray['one'], $this->extendedArray->one);
        $this->assertSame(
            $this->plainArray['one'],
            $this->extendedArray->offsetGet('one')
        );

        /**
         * OffsetGet int key with ExtendedArray element
         */
        $this->assertSame(
            $this->plainArray[0],
            $this->extendedArray->{0}->getArrayCopy()
        );
        $this->assertSame(
            $this->plainArray[0],
            $this->extendedArray->offsetGet(0)->getArrayCopy()
        );

        /**
         * OffsetGetFirst
         */
        $this->assertSame(
            reset($this->plainArray),
            $this->extendedArray->offsetGetFirst()
        );

        /**
         * OffsetGetLast
         */
        $this->assertSame(
            end($this->plainArray),
            $this->extendedArray->offsetGetLast()
        );

        /**
         * OffsetGetPosition
         */
        $this->seekKey($this->plainArray, 7); // pos 2
        $this->assertSame(
            current($this->plainArray),
            $this->extendedArray->OffsetGetPosition(2)
        );
    }

    /**
     * Test Offset Set / Unset
     *
     * @return null
     * @test   Offset Set / Unset
     */
    public function offsetSetUnset(): void
    {
        /**
         * OffsetSet existent key (simple)
         */
        $this->plainArray[7] = 'seven';
        $this->extendedArray->{7} = 'seven';
        $this->assertSame($this->plainArray[7], $this->extendedArray->{7});

        /**
         * OffsetSet existent key (set)
         */
        $this->plainArray[8] = 'eight';
        $this->extendedArray->offsetSet(8, 'eight');
        $this->assertSame($this->plainArray[8], $this->extendedArray->offsetGet(8));

        /**
         * OffsetSet new key (simple)
         */
        $this->plainArray['simple'] = $this->splFixedArray;
        $this->extendedArray->simple = $this->splFixedArray;
        $this->assertSame(
            $this->plainArray['simple'],
            $this->extendedArray->simple
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );

        /**
         * OffsetUnset new key (simple)
         */
        unset($this->plainArray['simple']);
        unset($this->extendedArray->simple);
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );

        /**
         * OffsetSet new key (set)
         */
        $this->plainArray['set'] = $this->splFixedArray;
        $this->extendedArray->offsetSet('set', $this->splFixedArray);
        $this->assertSame(
            $this->plainArray['set'],
            $this->extendedArray->offsetGet('set')
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );

        /**
         * OffsetUnset new key (set)
         */
        unset($this->plainArray['set']);
        $this->extendedArray->offsetUnset('set');
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
    }

    /**
     * Test Array Contains element
     *
     * @return null
     * @test   Array Contains element
     */
    public function arrayContainsElement(): void
    {
        $this->assertSame(
            in_array('four', $this->plainArray),
            $this->extendedArray->contains('four')
        );

        $byPass78756 = true; // https://bugs.php.net/bug.php?id=78756
        $this->assertSame(
            in_array($this->arrayObject, $this->plainArray, $byPass78756),
            $this->extendedArray->contains($this->arrayObject)
        );

        $this->assertSame(
            in_array($this->plainArray[0], $this->plainArray, true),
            $this->extendedArray->contains($this->extendedArray->{0}, true)
        );
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

    /**
     * Seek Key poly-fill for extended array
     *
     * @param array      $array to move cursor
     * @param int|string $key   to seek
     *
     * @return null
     */
    protected function seekKey(array &$array, $key): void
    {
        for (reset($array); !is_null(key($array)); next($array)) {
            if (key($array) === $key) {
                break;
            }
        }
    }
}
