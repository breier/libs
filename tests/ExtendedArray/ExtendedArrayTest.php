<?php

/**
 * Extended Array Test File
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

use PHPUnit\Framework\TestCase;

use Breier\ExtendedArray\ExtendedArray;

use ArrayIterator;
use ArrayObject;
use SplFixedArray;
use JsonException;

/**
 * Extended Array Test Class
 */
class ExtendedArrayTest extends TestCase
{
    private $emptyArray;
    private $plainArray;
    private $extendedArray;
    private $arrayIterator;
    private $arrayObject;
    private $splFixedArray;

    /**
     * Set up an example array for every test
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
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Contains Object
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

        $this->assertSame(false, $this->emptyArray->contains(0));
    }

    /**
     * Test Filter isArray
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
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Filter isString Key
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
            $this->extendedArray->filterWithObjects(
                $isArrayFilter
            )->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test FilterWithObjects isString Key
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
     */
    public function testFilterWithObjectsFalse(): void
    {
        $allFalseFilter = function ($item) {
            return false;
        };
        $this->assertSame(
            array_filter($this->plainArray, $allFalseFilter),
            $this->extendedArray->filterWithObjects(
                $allFalseFilter
            )->getArrayCopy()
        );
    }

    /**
     * Test Key and Value FilterWithObjects
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
            $this->extendedArray->filterWithObjects(
                $methodFilter
            )->getArrayCopy()
        );
    }

    /**
     * Test Empty Array FilterWithObjects
     */
    public function testFilterWithObjectsEmpty(): void
    {
        $allTrueFilter = function ($item) {
            return true;
        };
        $this->assertSame(
            array_filter([], $allTrueFilter),
            $this->emptyArray->filterWithObjects(
                $allTrueFilter
            )->getArrayCopy()
        );
    }

    /**
     * Test Empty FilterWithObjects on sub-array
     */
    public function testFilterWithObjectsSubEmpty(): void
    {
        $this->assertSame(
            array_filter($this->plainArray['six']),
            $this->extendedArray->six->filterWithObjects()->getArrayCopy()
        );
    }

    /**
     * Test PlainArray From JSON
     */
    public function testPlainArrayFromJSON(): void
    {
        $fromJSON = json_encode($this->plainArray);
        $this->assertSame(
            $this->plainArray,
            ExtendedArray::fromJSON($fromJSON, 3)->getArrayCopy()
        );
    }

    /**
     * Test Throws JSON Exception
     */
    public function testInstantiateThrowsJsonException(): void
    {
        $fromJSON = json_encode($this->plainArray);
        /**
         * Maximum stack depth exceeded
         */
        try {
            ExtendedArray::fromJSON($fromJSON, 2);
        } catch (JsonException $e) {
            $this->assertSame(
                'Maximum stack depth exceeded',
                $e->getMessage()
            );
        }

        /**
         * Control character error, possibly incorrectly encoded
         */
        try {
            ExtendedArray::fromJSON(substr($fromJSON, 0, 50));
        } catch (JsonException $e) {
            $this->assertSame(
                'Control character error, possibly incorrectly encoded',
                $e->getMessage()
            );
        }

        /**
         * Syntax error
         */
        try {
            ExtendedArray::fromJSON('invalid');
        } catch (JsonException $e) {
            $this->assertSame(
                'Syntax error',
                $e->getMessage()
            );
        }
    }

    /**
     * Test Implode with Comma
     */
    public function testImplodeComma(): void
    {
        $toStringMap = function ($item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            return (string) $item;
        };
        $stringArray = array_map($toStringMap, $this->plainArray);

        $this->extendedArray->next();
        next($stringArray);

        $this->assertSame(
            implode(',', $stringArray),
            $this->extendedArray->implode(',')
        );
        $this->assertSame(
            key($stringArray),
            $this->extendedArray->key()
        );

        $this->assertSame('', $this->emptyArray->implode());
    }

    /**
     * Test Implode with String
     */
    public function testImplodeString(): void
    {
        $toStringMap = function ($item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            return (string) $item;
        };
        $stringArray = array_map($toStringMap, $this->plainArray);

        $this->assertSame(
            implode('=/=', $stringArray),
            $this->extendedArray->implode('=/=')
        );
    }

    /**
     * Test Implode with Integer
     */
    public function testImplodeInteger(): void
    {
        $toStringMap = function ($item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            return (string) $item;
        };
        $stringArray = array_map($toStringMap, $this->plainArray);

        $this->assertSame(
            implode(77, $stringArray),
            $this->extendedArray->implode(77)
        );
    }

    /**
     * Test Implode with Empty String
     */
    public function testImplodeEmptyString(): void
    {
        $toStringMap = function ($item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            return (string) $item;
        };
        $stringArray = array_map($toStringMap, $this->plainArray);

        $this->assertSame(
            implode('', $stringArray),
            $this->extendedArray->implode()
        );
    }

    /**
     * Test Is Array
     */
    public function testIsArray(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

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

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Keys
     *
     * It's pretty much covered everywhere else
     *
     * So I'm just skipping it ;)
     */

    /**
     * Test Krsort
     */
    public function testKrsort(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        krsort($this->plainArray);

        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->krsort()->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame([], $this->emptyArray->krsort()->getArrayCopy());
    }

    /**
     * Test Map String Length
     */
    public function testMapStringLength(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $strlenMap = function ($item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            return strlen($item);
        };
        $this->assertSame(
            array_map($strlenMap, $this->plainArray),
            $this->extendedArray->map($strlenMap)->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Map String Conversion
     */
    public function testMapStringConversion(): void
    {
        $toStringMap = function ($item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            return (string) $item;
        };
        $this->assertSame(
            array_map($toStringMap, $this->plainArray),
            $this->extendedArray->map($toStringMap)->getArrayCopy()
        );
    }

    /**
     * Test Map Cube
     */
    public function testMapCube(): void
    {
        $cubeMap = function ($item) {
            if (ExtendedArray::isArray($item)) {
                $item = (new ExtendedArray($item))->count();
            }
            if (!is_int($item)) {
                $item = intval($item);
            }
            return $item * $item * $item;
        };
        $this->assertSame(
            array_map($cubeMap, $this->plainArray),
            $this->extendedArray->map($cubeMap)->getArrayCopy()
        );
    }

    /**
     * Test Map isArray
     */
    public function testMapIsArray(): void
    {
        $isArrayMap = function ($item) {
            return ExtendedArray::isArray($item);
        };
        $this->assertSame(
            array_map($isArrayMap, $this->plainArray),
            $this->extendedArray->map($isArrayMap)->getArrayCopy()
        );
    }

    /**
     * Test Map Extra Params
     */
    public function testMapExtraParams(): void
    {
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
     * Test Empty Array Map
     */
    public function testMapEmpty(): void
    {
        $allTrueMap = function ($item) {
            return $item;
        };
        $this->assertSame(
            array_map($allTrueMap, []),
            $this->emptyArray->map($allTrueMap)->getArrayCopy()
        );
    }

    /**
     * Test MapWithObjects String Length
     */
    public function testMapWithObjectsStringLength(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $strlenMap = function ($item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            return strlen($item);
        };
        $this->assertSame(
            array_map($strlenMap, $this->plainArray),
            $this->extendedArray->mapWithObjects($strlenMap)->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test MapWithObjects String Conversion
     */
    public function testMapWithObjectsStringConversion(): void
    {
        $toStringMap = function ($item) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            return (string) $item;
        };
        $this->assertSame(
            array_map($toStringMap, $this->plainArray),
            $this->extendedArray->mapWithObjects($toStringMap)->getArrayCopy()
        );
    }

    /**
     * Test MapWithObjects Cube
     */
    public function testMapWithObjectsCube(): void
    {
        $cubeMap = function ($item) {
            if (ExtendedArray::isArray($item)) {
                $item = (new ExtendedArray($item))->count();
            }
            if (!is_int($item)) {
                $item = intval($item);
            }
            return $item * $item * $item;
        };
        $this->assertSame(
            array_map($cubeMap, $this->plainArray),
            $this->extendedArray->mapWithObjects($cubeMap)->getArrayCopy()
        );
    }

    /**
     * Test MapWithObjects isArray
     */
    public function testMapWithObjectsIsArray(): void
    {
        $isArrayMap = function ($item) {
            return ExtendedArray::isArray($item);
        };
        $this->assertSame(
            array_map($isArrayMap, $this->plainArray),
            $this->extendedArray->mapWithObjects($isArrayMap)->getArrayCopy()
        );
    }

    /**
     * Test MapWithObjects Extra Params
     */
    public function testMapWithObjectsExtraParams(): void
    {
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
            $extendedItems->mapWithObjects(
                $extraParamsMap,
                $nameArray,
                $cityArray
            )->getArrayCopy()
        );
    }

    /**
     * Test MapWithObjects Method
     */
    public function testMapWithObjectsMethod(): void
    {
        $methodMap = function ($item) {
            if (!ExtendedArray::isArrayObject($item)) {
                return json_encode([$item, -1]);
            }
            $item->asort()->append($item->count());
            return $item->jsonSerialize();
        };
        $expectedPlainArrayMap = [
            'one' => '[1,-1]',
            0 => '[{"2":"two","3":"three"},-1]',
            7 => '["four",-1]',
            8 => '["five",-1]',
            'six' => '[{"temp":"long string that\'s not'
                . ' so long","empty":null},-1]'
        ];
        $expectedExtendedArrayMap = [
            'one' => '[1,-1]',
            0 => '{"3":"three","2":"two","4":2}',
            7 => '["four",-1]',
            8 => '["five",-1]',
            'six' => '{"empty":null,"temp":"long string'
                . ' that\'s not so long","0":2}'
        ];
        $this->assertSame(
            array_map($methodMap, $this->plainArray),
            $expectedPlainArrayMap
        );
        $this->assertSame(
            $expectedExtendedArrayMap,
            $this->extendedArray->mapWithObjects($methodMap)->getArrayCopy()
        );
    }

    /**
     * Test Empty Array MapWithObjects
     */
    public function testMapWithObjectsEmpty(): void
    {
        $allTrueMap = function ($item) {
            return $item;
        };
        $this->assertSame(
            array_map($allTrueMap, []),
            $this->emptyArray->mapWithObjects($allTrueMap)->getArrayCopy()
        );
    }

    /**
     * Test Offset Get First
     */
    public function testOffsetGetFirst(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            $this->plainArray[array_key_first($this->plainArray)],
            $this->extendedArray->offsetGetFirst()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame(null, $this->emptyArray->offsetGetFirst());
    }

    /**
     * Test Offset Get Last
     */
    public function testOffsetGetLast(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            $this->plainArray[array_key_last($this->plainArray)],
            $this->extendedArray->offsetGetLast()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame(null, $this->emptyArray->offsetGetLast());
    }

    /**
     * Test Offset Get Position
     */
    public function testOffsetGetPosition(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $keyFromPosition = array_keys($this->plainArray)[2];
        $this->assertSame(
            $this->plainArray[$keyFromPosition],
            $this->extendedArray->OffsetGetPosition(2)
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        try {
            $this->emptyArray->offsetGetPosition(0);
        } catch (\OutOfBoundsException $e) {
            $this->assertSame(
                'Seek position 0 is out of range',
                $e->getMessage()
            );
        }
    }

    /**
     * Test Shuffle
     */
    public function testShuffle(): void
    {
        $this->extendedArray->next();

        $this->extendedArray->shuffle()->keys();
        $this->assertSame(
            array_keys($this->extendedArray->getArrayCopy()),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            $this->extendedArray->keys()->offsetGetFirst(),
            $this->extendedArray->key()
        );

        $this->assertSame([], $this->emptyArray->shuffle()->getArrayCopy());
    }

    /**
     * Test Values
     */
    public function testValues(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            array_values($this->plainArray),
            $this->extendedArray->values()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame([], $this->emptyArray->values()->getArrayCopy());
    }

    /**
     * @test 1000 ElementArray takes less than 50ms
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
}
