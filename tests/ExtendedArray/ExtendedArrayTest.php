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
    protected $emptyArray;
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
                'temp' => 'long string that\'s not so long',
                'empty' => null
            ],
        ];

        $this->emptyArray = new ExtendedArray();
        $this->extendedArray = new ExtendedArray($this->plainArray);
        $this->arrayIterator = new ArrayIterator($this->plainArray);
        $this->arrayObject = new ArrayObject($this->plainArray);
        $this->splFixedArray = SplFixedArray::fromArray(
            array_values($this->plainArray)
        );
    }

    /**
     * Test Arsort
     *
     * @return null
     */
    public function testArsort(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        arsort($this->plainArray);

        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->arsort()->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame([], $this->emptyArray->arsort()->getArrayCopy());
    }

    /**
     * Test Contains String
     *
     * @return null
     */
    public function testContainsString(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            in_array('four', $this->plainArray),
            $this->extendedArray->contains('four')
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Contains Object
     *
     * @return null
     */
    public function testContainsObject(): void
    {
        $byPass78756 = true; // https://bugs.php.net/bug.php?id=78756
        $this->assertSame(
            in_array($this->arrayObject, $this->plainArray, $byPass78756),
            $this->extendedArray->contains($this->arrayObject)
        );
    }

    /**
     * Test Contains Own
     *
     * @return null
     */
    public function testContainsOwn(): void
    {
        $plainElementZero = $this->plainArray[0];
        $extendedElementZero = $this->extendedArray->{0};
        $this->assertSame(
            in_array($plainElementZero, $this->plainArray, true),
            $this->extendedArray->contains($extendedElementZero, true)
        );
    }

    /**
     * Test Contains Integer
     *
     * @return null
     */
    public function testContainsInteger(): void
    {
        $this->assertSame(
            in_array(2019, $this->plainArray),
            $this->extendedArray->contains(2019)
        );
    }

    /**
     * Test Contains Other
     *
     * @return null
     */
    public function testContainsOther(): void
    {
        $this->assertSame(
            in_array(0, $this->plainArray),
            $this->extendedArray->contains(0)
        );
        $this->assertSame(
            in_array(0, $this->plainArray, true),
            $this->extendedArray->contains(0, true)
        );

        $this->assertSame(false, $this->emptyArray->contains('anything'));
    }

    /**
     * Test Filter isArray
     *
     * @return null
     */
    public function testFilterIsArray(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $isArrayFilter = function ($item) {
            return ExtendedArray::isArray($item);
        };
        $this->assertSame(
            array_filter($this->plainArray, $isArrayFilter),
            $this->extendedArray->filter($isArrayFilter)->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Filter isString Key
     *
     * @return null
     */
    public function testFilterIsStringKey(): void
    {
        $isStringFilter = function ($item) {
            return is_string($item);
        };
        $this->assertSame(
            array_filter(
                $this->plainArray,
                $isStringFilter,
                ARRAY_FILTER_USE_KEY
            ),
            $this->extendedArray->filter(
                $isStringFilter,
                ARRAY_FILTER_USE_KEY
            )->getArrayCopy()
        );
    }

    /**
     * Test Filter False
     *
     * @return null
     */
    public function testFilterFalse(): void
    {
        $allFalseFilter = function ($item) {
            return false;
        };
        $this->assertSame(
            array_filter($this->plainArray, $allFalseFilter),
            $this->extendedArray->filter($allFalseFilter)->getArrayCopy()
        );
    }

    /**
     * Test Key and Value Filter
     *
     * @return null
     */
    public function testFilterKeyValue(): void
    {
        $keyValueFilter = function ($value, $key) {
            return (is_numeric($key) && ExtendedArray::isArray($value));
        };
        $this->assertSame(
            array_filter(
                $this->plainArray,
                $keyValueFilter,
                ARRAY_FILTER_USE_BOTH
            ),
            $this->extendedArray->filter(
                $keyValueFilter,
                ARRAY_FILTER_USE_BOTH
            )->getArrayCopy()
        );
    }

    /**
     * Test Empty Array Filter
     *
     * @return null
     */
    public function testFilterEmpty(): void
    {
        $allTrueFilter = function ($item) {
            return true;
        };
        $this->assertSame(
            array_filter([], $allTrueFilter),
            $this->emptyArray->filter($allTrueFilter)->getArrayCopy()
        );
    }

    /**
     * Test Empty Filter on sub-array
     *
     * @return null
     */
    public function testFilterSubEmpty(): void
    {
        $this->assertSame(
            array_filter($this->plainArray['six']),
            $this->extendedArray->six->filter()->getArrayCopy()
        );
    }

    /**
     * Test FilterWithObjects isArray
     *
     * @return null
     */
    public function testFilterWithObjectsIsArray(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $isArrayFilter = function ($item) {
            return ExtendedArray::isArray($item);
        };
        $this->assertSame(
            array_filter($this->plainArray, $isArrayFilter),
            $this->extendedArray->filterWithObjects($isArrayFilter)->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test FilterWithObjects isString Key
     *
     * @return null
     */
    public function testFilterWithObjectsIsStringKey(): void
    {
        $isStringFilter = function ($item) {
            return is_string($item);
        };
        $this->assertSame(
            array_filter(
                $this->plainArray,
                $isStringFilter,
                ARRAY_FILTER_USE_KEY
            ),
            $this->extendedArray->filterWithObjects(
                $isStringFilter,
                ARRAY_FILTER_USE_KEY
            )->getArrayCopy()
        );
    }

    /**
     * Test FilterWithObjects False
     *
     * @return null
     */
    public function testFilterWithObjectsFalse(): void
    {
        $allFalseFilter = function ($item) {
            return false;
        };
        $this->assertSame(
            array_filter($this->plainArray, $allFalseFilter),
            $this->extendedArray->filterWithObjects($allFalseFilter)->getArrayCopy()
        );
    }

    /**
     * Test Key and Value FilterWithObjects
     *
     * @return null
     */
    public function testFilterWithObjectsKeyValue(): void
    {
        $keyValueFilter = function ($value, $key) {
            return (is_numeric($key) && ExtendedArray::isArray($value));
        };
        $this->assertSame(
            array_filter(
                $this->plainArray,
                $keyValueFilter,
                ARRAY_FILTER_USE_BOTH
            ),
            $this->extendedArray->filterWithObjects(
                $keyValueFilter,
                ARRAY_FILTER_USE_BOTH
            )->getArrayCopy()
        );
    }

    /**
     * Test Methods FilterWithObjects
     *
     * @return null
     */
    public function testFilterWithObjectsMethod(): void
    {
        $methodFilter = function ($value) {
            if (!ExtendedArray::isArrayObject($value)) {
                return false;
            }
            return $value->contains('two');
        };
        $this->assertSame(
            array_filter($this->plainArray, $methodFilter),
            []
        );
        $this->assertSame(
            [0 => [2 => 'two', 'three']],
            $this->extendedArray->filterWithObjects($methodFilter)->getArrayCopy()
        );
    }

    /**
     * Test Empty Array FilterWithObjects
     *
     * @return null
     */
    public function testFilterWithObjectsEmpty(): void
    {
        $allTrueFilter = function ($item) {
            return true;
        };
        $this->assertSame(
            array_filter([], $allTrueFilter),
            $this->emptyArray->filterWithObjects($allTrueFilter)->getArrayCopy()
        );
    }

    /**
     * Test Empty FilterWithObjects on sub-array
     *
     * @return null
     */
    public function testFilterWithObjectsSubEmpty(): void
    {
        $this->assertSame(
            array_filter($this->plainArray['six']),
            $this->extendedArray->six->filterWithObjects()->getArrayCopy()
        );
    }

    /**
     * Test extended array instantiates from JSON
     *
     * Pending review from here on
     *
     * @return null
     * @test   extended array instantiates from JSON
     */
    public function instantiateFromJSON(): void
    {
        $fromJSON = json_encode($this->plainArray);
        $this->assertSame(
            $this->plainArray,
            ExtendedArray::fromJSON($fromJSON)->getArrayCopy()
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
     * Test returned correct mapped array
     *
     * @return null
     * @test   returned correct mapped array
     */
    public function returnedCorrectMappedArray(): void
    {
        /**
         * String Length Mapping
        */
        $strlenMap = function ($item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            return strlen($item);
        };
        next($this->plainArray);
        $this->extendedArray->next();
        $this->assertSame(
            array_map($strlenMap, $this->plainArray),
            $this->extendedArray->map($strlenMap)->getArrayCopy()
        );
        $this->assertSame(key($this->plainArray), $this->extendedArray->key());

        /**
         * String Conversion Mapping
        */
        $toStringMap = function ($item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            return (string) $item;
        };
        next($this->plainArray);
        $this->extendedArray->next();
        $this->assertSame(
            array_map($toStringMap, $this->plainArray),
            $this->extendedArray->map($toStringMap)->getArrayCopy()
        );
        $this->assertSame(key($this->plainArray), $this->extendedArray->key());

        /**
         * Cube Mapping
        */
        $cubeMap = function ($item) {
            if (ExtendedArray::isArray($item)) {
                $item = (new ExtendedArray($item))->count();
            }
            if (!is_int($item)) {
                $item = intval($item);
            }
            return $item * $item * $item;
        };
        next($this->plainArray);
        $this->extendedArray->next();
        $this->assertSame(
            array_map($cubeMap, $this->plainArray),
            $this->extendedArray->map($cubeMap)->getArrayCopy()
        );
        $this->assertSame(key($this->plainArray), $this->extendedArray->key());

        /**
         * Is Array Map
        */
        $isArrayMap = function ($item) {
            return ExtendedArray::isArray($item);
        };
        next($this->plainArray);
        $this->extendedArray->next();
        $this->assertSame(
            array_map($isArrayMap, $this->plainArray),
            $this->extendedArray->map($isArrayMap)->getArrayCopy()
        );
        $this->assertSame(key($this->plainArray), $this->extendedArray->key());

        /**
         * Extra Params Map
         */
        $extraParamsMap = function ($item, $name, $city) {
            return [$item => [$name => $city]];
        };
        $itemArray = [99, 27, 43, 56];
        $nameArray = ['Umbrela', 'Lolypop', 'Tire', 'Cap'];
        $cityArray = ['Dublin', 'Paris', 'Alabama', 'Chicago'];
        $extendedItems = new ExtendedArray($itemArray);
        $this->assertSame(
            array_map(
                $extraParamsMap,
                $itemArray,
                $nameArray,
                $cityArray
            ),
            $extendedItems->map(
                $extraParamsMap,
                $nameArray,
                $cityArray
            )->getArrayCopy()
        );
    }

    /**
     * Test array values return as expected
     *
     * @return null
     * @test   array values return as expected
     */
    public function returnedValuesAsExpected(): void
    {
        $this->assertSame(
            array_values($this->plainArray),
            $this->extendedArray->values()->getArrayCopy()
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
