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
use JsonException;

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
            'five',
            'six' => [
                'temp' => 'long string that\'s not so long' ,
                'empty' => null
            ]
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
     * Test throws for invalid JSON
     *
     * @return null
     * @test   throws for invalid JSON
     */
    public function throwsForInvalidJSON(): void
    {
        $this->expectException(JsonException::class);

        ExtendedArray::fromJSON('invalid');
    }

    /**
     * Test throws for broken JSON
     *
     * @return null
     * @test   throws for broken JSON
     */
    public function throwsForBrokenJSON(): void
    {
        $this->expectException(JsonException::class);

        ExtendedArray::fromJSON(
            substr($this->extendedArray->jsonSerialize(), 0, 50)
        );
    }

    /**
     * Test returned the correct a-sorted array
     *
     * @return null
     * @test   returned the correct a-sorted array
     */
    public function returnsCorrectASortedArray(): void
    {
        asort($this->plainArray);
        $this->extendedArray->asort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
    }

    /**
     * Test returned the correct a-r-sorted array
     *
     * @return null
     * @test   returned the correct a-r-sorted array
     */
    public function returnsCorrectARSortedArray(): void
    {
        arsort($this->plainArray);
        $this->extendedArray->arsort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
    }

    /**
     * Test returned the correct k-sorted array
     *
     * @return null
     * @test   returned the correct k-sorted array
     */
    public function returnsCorrectKSortedArray(): void
    {
        ksort($this->plainArray);
        $this->extendedArray->ksort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
    }

    /**
     * Test returned the correct k-r-sorted array
     *
     * @return null
     * @test   returned the correct k-r-sorted array
     */
    public function returnsCorrectKRSortedArray(): void
    {
        krsort($this->plainArray);
        $this->extendedArray->krsort();
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
    }

    /**
     * Test returned the correct u-a-sorted array
     *
     * @return null
     * @test   returned the correct u-a-sorted array
     */
    public function returnsCorrectUASortedArray(): void
    {
        uasort(
            $this->plainArray,
            function ($a, $b) {
                return md5(json_encode($a)) <=> md5(json_encode($b));
            }
        );
        $this->extendedArray->uasort(
            function ($a, $b) {
                return md5(json_encode($a)) <=> md5(json_encode($b));
            }
        );
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
    }

    /**
     * Test returned the correct u-k-sorted array
     *
     * @return null
     * @test   returned the correct u-k-sorted array
     */
    public function returnsCorrectUKSortedArray(): void
    {
        uksort(
            $this->plainArray,
            function ($a, $b) {
                return md5(json_encode($a)) <=> md5(json_encode($b));
            }
        );
        $this->extendedArray->uksort(
            function ($a, $b) {
                return md5(json_encode($a)) <=> md5(json_encode($b));
            }
        );
        $this->assertSame($this->plainArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
    }

    /**
     * Test returned the correct nat-case-sorted array
     *
     * @return null
     * @test   returned the correct nat-case-sorted array
     */
    public function returnsCorrectNatCaseSortedArray(): void
    {
        $jsonStringifiedArray = $this->plainArray;
        $jsonStringifiedArray[0] = json_encode($jsonStringifiedArray[0]);
        $jsonStringifiedArray['six'] = json_encode($jsonStringifiedArray['six']);
        natcasesort($jsonStringifiedArray);
        $jsonStringifiedArray['six'] = json_decode($jsonStringifiedArray['six'], true);
        $jsonStringifiedArray[0] = json_decode($jsonStringifiedArray[0], true);
        $this->extendedArray->natcasesort();
        $this->assertSame($jsonStringifiedArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($jsonStringifiedArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
    }

    /**
     * Test returned the correct nat-sorted array
     *
     * @return null
     * @test   returned the correct nat-sorted array
     */
    public function returnsCorrectNatSortedArray(): void
    {
        $jsonStringifiedArray = $this->plainArray;
        $jsonStringifiedArray[0] = json_encode($jsonStringifiedArray[0]);
        $jsonStringifiedArray['six'] = json_encode($jsonStringifiedArray['six']);
        natsort($jsonStringifiedArray);
        $jsonStringifiedArray['six'] = json_decode($jsonStringifiedArray['six'], true);
        $jsonStringifiedArray[0] = json_decode($jsonStringifiedArray[0], true);
        $this->extendedArray->natsort();
        $this->assertSame($jsonStringifiedArray, $this->extendedArray->getArrayCopy());
        $this->assertSame(
            array_keys($jsonStringifiedArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
    }

    /**
     * Test returned the correct shuffled array
     *
     * @return null
     * @test   returned the correct shuffled array
     */
    public function returnsCorrectShuffledArray(): void
    {
        $this->extendedArray->shuffle();
        $this->assertSame(
            array_keys($this->extendedArray->getArrayCopy()),
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
        $this->assertSame(
            $this->plainArray['one'],
            $this->getItem($this->extendedArray->one)
        );
        $this->assertSame(
            $this->plainArray['one'],
            $this->getItem($this->extendedArray->offsetGet('one'))
        );

        /**
         * OffsetGet int key with ExtendedArray element
         */
        $this->assertSame(
            $this->plainArray[0],
            $this->getItem($this->extendedArray->{0})
        );
        $this->assertSame(
            $this->plainArray[0],
            $this->getItem($this->extendedArray->offsetGet(0))
        );

        /**
         * OffsetGetFirst
         */
        $this->assertSame(
            reset($this->plainArray),
            $this->getItem($this->extendedArray->offsetGetFirst())
        );

        /**
         * OffsetGetLast
         */
        $this->assertSame(
            end($this->plainArray),
            $this->getItem($this->extendedArray->offsetGetLast())
        );

        /**
         * OffsetGetPosition
         */
        $this->seekKey($this->plainArray, 7); // pos 2
        $this->assertSame(
            current($this->plainArray),
            $this->getItem($this->extendedArray->OffsetGetPosition(2))
        );
    }

    /**
     * Test simple set / unset
     *
     * @return null
     * @test   simple set / unset
     */
    public function simpleSetUnset(): void
    {
        /**
         * OffsetSet existent key (simple)
         */
        $this->plainArray[7] = 'seven';
        $this->extendedArray->{7} = 'seven';
        $this->assertSame($this->plainArray[7], $this->extendedArray->{7});

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
         * OffsetSet existent key (set)
         */
        $this->plainArray[8] = 'eight';
        $this->extendedArray->offsetSet(8, 'eight');
        $this->assertSame($this->plainArray[8], $this->extendedArray->offsetGet(8));

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
     * Test Append element
     *
     * @return null
     * @test   append element
     */
    public function appendElement(): void
    {
        array_push($this->plainArray, 'appended element');
        $this->extendedArray->append('appended element');
        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->getArrayCopy()
        );
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
        /**
         * Simple comparison, expects TRUE
         */
        $this->assertSame(
            in_array('four', $this->plainArray),
            $this->extendedArray->contains('four')
        );

        /**
         * Object comparison, expects FALSE
         */
        $byPass78756 = true; // https://bugs.php.net/bug.php?id=78756
        $this->assertSame(
            in_array($this->arrayObject, $this->plainArray, $byPass78756),
            $this->extendedArray->contains($this->arrayObject)
        );

        /**
         * Advanced comparison, expects TRUE
         */
        $plainElementZero = $this->plainArray[0];
        $extendedElementZero = $this->extendedArray->{0};
        $this->assertSame(
            in_array($plainElementZero, $this->plainArray, true),
            $this->extendedArray->contains($extendedElementZero, true)
        );

        /**
         * Integer comparison, expects FALSE
         */
        $this->assertSame(
            in_array(2019, $this->plainArray),
            $this->extendedArray->contains(2019)
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
        /**
         * Default JSON
         */
        $plainArrayJSON = json_encode($this->plainArray);
        $extendedArrayJSON = $this->extendedArray->jsonSerialize();

        $this->assertSame($plainArrayJSON, $extendedArrayJSON);

        /**
         * Pretty JSON
         */
        $plainArrayPrettyJSON = json_encode(
            $this->plainArray,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
        $extendedArrayPrettyJSON = $this->extendedArray->jsonSerialize(
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        $this->assertSame($plainArrayPrettyJSON, $extendedArrayPrettyJSON);
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

        $this->extendedArray->asort();
        $this->extendedArray->arsort();
        $this->extendedArray->ksort();
        $this->extendedArray->krsort();
        $this->extendedArray->natsort();
        $this->extendedArray->shuffle();
        $this->extendedArray->natcasesort();

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

    /**
     * Get item from extended array
     *
     * @param mixed $item to be returned
     *
     * @return mixed
     */
    public function getItem($item)
    {
        return ExtendedArray::isArrayObject($item)
            ? $item->getArrayCopy()
            : $item;
    }
}
