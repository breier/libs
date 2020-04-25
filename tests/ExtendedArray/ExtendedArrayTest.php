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
 * @link     php vendor/bin/phpunit tests/ExtendedArray/ExtendedArrayTest.php
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
     * Test Contains
     *
     * @dataProvider containsProvider
     */
    public function testContains($parameter, $strict = false): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            in_array($parameter, $this->plainArray, $strict),
            $this->extendedArray->contains($parameter, $strict)
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Contains Provider
     */
    public function containsProvider(): array
    {
        $this->setUp();

        return [
            'success-zero' => ['parameter' => 0],
            'success-one' => ['parameter' => 'four'],
            'success-bypass-78756' => [
                'parameter' => $this->arrayObject,
                'strict' => true, // https://bugs.php.net/bug.php?id=78756
            ],
            'success-strict-zero' => [
                'parameter' => 0,
                'strict' => true,
            ],
            'fail-strict-int' => ['parameter' => 2019],
            'fail-strict-bool' => ['parameter' => false],
        ];
    }

    /**
     * Test Array Diff
     *
     * @dataProvider arrayDiffProvider
     */
    public function testArrayDIff($exec, $exception = null): void
    {
        if ($exception) {
            try {
                $this->extendedArray->diff('non-array');
    
                $this->assertTrue(false); // Hasn't thrown an exception
            } catch (\InvalidArgumentException $e) {
                $this->assertSame($exception, $e->getMessage());
            }
        }

        $flaten = function ($item) {
            return is_array($item) ? json_encode($item) : $item;
        };

        $array1 = array_map($flaten, $this->plainArray);
        $array2 = array_map($flaten, $this->plainArray);

        $extendedArray2 = new ExtendedArray($this->extendedArray);
        eval($exec);

        $arrayDiffAsc = isset($array3)
            ? array_diff($array1, $array2, $array3)
            : array_diff($array1, $array2);
        $extendedArrayDiffAsc =  isset($extendedArray3)
            ? $this->extendedArray->diff($extendedArray2, $extendedArray3)
                ->map($flaten)->getArrayCopy()
            : $this->extendedArray->diff($extendedArray2)->map($flaten)->getArrayCopy();

        $this->assertSame($arrayDiffAsc, $extendedArrayDiffAsc);

        $arrayDiffDesc = isset($array3)
            ? array_diff($array3, $array2, $array1)
            : array_diff($array2, $array1);
        $extendedArrayDiffDesc =  isset($extendedArray3)
            ? $extendedArray3->diff($extendedArray2, $this->extendedArray)
                ->map($flaten)->getArrayCopy()
            : $extendedArray2->diff($this->extendedArray)->map($flaten)->getArrayCopy();

        $this->assertSame($arrayDiffDesc, $extendedArrayDiffDesc);
    }

    /**
     * Array Diff Provider
     */
    public function arrayDiffProvider(): array
    {
        $this->setUp();

        return [
            'success-smaller' => [
                'exec' => 'unset($array2[0]); $extendedArray2->offsetUnset(0);',
            ],
            'success-bigger' => [
                'exec' => '$array2[\'new_el\'] = \'My Extra Element\';'
                    . ' $extendedArray2->offsetSet(\'new_el\', \'My Extra Element\');',
            ],
            'success-three' => [
                'exec' => 'unset($array2[0], $array2[7]);'
                    . ' $array3 = array_map($flaten, $this->plainArray);'
                    . ' unset($array3[7]);'
                    . ' $extendedArray2->offsetUnset(0);'
                    . ' $extendedArray2->offsetUnset(7);'
                    . ' $extendedArray3 = new \Breier\ExtendedArray\ExtendedArray($this->extendedArray);'
                    . ' $extendedArray3->offsetUnset(7);',
            ],
            'fail-non-array' => [
                'exec' => '',
                'exception' => 'Only array types are accepted as parameter!',
            ],
        ];
    }

    /**
     * Test Explode
     *
     * @dataProvider explodeProvider
     */
    public function testExplode($glue, $string, $limit = 256): void
    {
        $this->assertSame(
            explode($glue, $string, $limit),
            ExtendedArray::explode($glue, $string, $limit)->getArrayCopy()
        );
    }

    /**
     * Explode Provider
     */
    public function explodeProvider(): array
    {
        return [
            'space-simple' => [
                'glue' => ' ',
                'string' => 'long string that\'s not so long',
            ],
            'space-limit' => [
                'glue' => ' ',
                'string' => 'long string that\'s not so long',
                'limit' => 3
            ],
            'that-simple' => [
                'glue' => 'that',
                'string' => 'long string that\'s not so long',
            ],
            'not-found-int' => [
                'glue' => 77,
                'string' => 'long string that\'s not so long',
            ],
            'not-found-empty' => [
                'glue' => '"',
                'string' => '',
            ],
        ];
    }

    /**
     * Test Array Fill
     */
    public function testArrayFill(): void
    {
        $this->assertSame(
            array_fill(0, 5, 123),
            ExtendedArray::fill(0, 5, 123)->getArrayCopy()
        );

        $this->assertSame(
            array_fill(5, 5, 'string'),
            ExtendedArray::fill(5, 5, 'string')->getArrayCopy()
        );

        $this->assertSame(
            array_fill(-10, 2, 321),
            ExtendedArray::fill(-10, 2, 321)->getArrayCopy()
        );
    }

    /**
     * Test Filter
     *
     * @dataProvider filterProvider
     */
    public function testFilter($empty, $callable, $flag = 0): void
    {
        $plainArray = $empty ? [] : $this->plainArray;
        $extendedArray = $empty ? $this->emptyArray : $this->extendedArray;

        $extendedArray->next();
        next($plainArray);

        $arrayFilter = $callable
            ? array_filter($plainArray, $callable, $flag)
            : array_filter($plainArray);
        $extendedArrayFilter = $callable
            ? $extendedArray->filter($callable, $flag)->getArrayCopy()
            : $extendedArray->filter()->getArrayCopy();

        $this->assertSame($arrayFilter, $extendedArrayFilter);
        $this->assertSame(key($plainArray), $extendedArray->key());
    }

    /**
     * Filter Provider
     */
    public function filterProvider(): array
    {
        return [
            'is-array' => [
                'empty' => false,
                'callable' => function ($item) {
                    return ExtendedArray::isArray($item);
                },
            ],
            'is-string-key' => [
                'empty' => false,
                'callable' => 'is_string',
                'flag' => ARRAY_FILTER_USE_KEY,
            ],
            'all-false' => [
                'empty' => false,
                'callable' => function ($item) {
                    return false;
                },
            ],
            'is-numeric-array-both' => [
                'empty' => false,
                'callable' => function ($value, $key) {
                    return (is_numeric($key) && ExtendedArray::isArray($value));
                },
                'flag' => ARRAY_FILTER_USE_BOTH,
            ],
            'empty-array' => [
                'empty' => true,
                'callable' => function ($item) {
                    return true;
                },
            ],
            'empty-callable' => ['empty' => false, 'callable' => null],
        ];
    }

    /**
     * Test FilterWithObjects
     *
     * @dataProvider filterWithObjectsProvider
     */
    public function testFilterWithObjects($empty, $callable, $flag = 0): void
    {
        $plainArray = $empty ? [] : $this->plainArray;
        $extendedArray = $empty ? $this->emptyArray : $this->extendedArray;

        $extendedArray->next();
        next($plainArray);

        $arrayFilter = $callable
            ? array_filter($plainArray, $callable, $flag)
            : array_filter($plainArray);
        $extendedArrayFilter = $callable
            ? $extendedArray->filterWithObjects($callable, $flag)->getArrayCopy()
            : $extendedArray->filterWithObjects()->getArrayCopy();

        $this->assertSame($arrayFilter, $extendedArrayFilter);
        $this->assertSame(key($plainArray), $extendedArray->key());
    }

    /**
     * Filter With Objects Provider
     */
    public function filterWithObjectsProvider(): array
    {
        return array_merge(
            $this->filterProvider(),
            [
                'method-contains' => [
                    'empty' => false,
                    'callable' => function ($item) {
                        if (!ExtendedArray::isArrayObject($item) && !is_array($item)) {
                            return false;
                        }
                        return is_array($item)
                            ? array_search('two', $item)
                            : $item->contains('two');
                    }
                ],
            ]
        );
    }

    /**
     * Test From JSON
     *
     * @dataProvider fromJSONProvider
     */
    public function testFromJSON($jsonString, $limit, $exception = null): void
    {
        try {
            $resultArray = ExtendedArray::fromJSON($jsonString, $limit);

            if ($exception) {
                $this->assertTrue(false); // Hasn't thrown an exception
            } else {
                $this->assertSame($this->plainArray, $resultArray->getArrayCopy());
            }
        } catch (JsonException $e) {
            $this->assertSame($exception, $e->getMessage());
        }
    }

    /**
     * From JSON Provider
     */
    public function fromJSONProvider(): array
    {
        $this->setUp();

        return [
            'plain-array' => [
                'jsonString' => json_encode($this->plainArray),
                'limit' => 3,
            ],
            'limit-exception' => [
                'jsonString' => json_encode($this->plainArray),
                'limit' => 2,
                'exception' => 'Maximum stack depth exceeded',
            ],
            'control-exception' => [
                'jsonString' => substr(json_encode($this->plainArray), 0, 50),
                'limit' => 3,
                'exception' => 'Control character error, possibly incorrectly encoded',
            ],
            'invalid-exception' => [
                'jsonString' => 'invalid',
                'limit' => 1,
                'exception' => 'Syntax error',
            ],
        ];
    }

    /**
     * Test Implode
     *
     * @dataProvider implodeProvider
     */
    public function testImplode($glue): void
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
            implode($glue, $stringArray),
            $this->extendedArray->implode($glue)
        );
        $this->assertSame(
            key($stringArray),
            $this->extendedArray->key()
        );

        $this->assertSame('', $this->emptyArray->implode());
    }

    /**
     * Implode Provider
     */
    public function implodeProvider(): array
    {
        return [
            'comma' => ['glue' => ','],
            'composed' => ['glue' => '=/='],
            'integer' => ['glue' => 77],
            'empty' => ['glue' => ''],
        ];
    }

    /**
     * Test Is Array
     *
     * @dataProvider isArrayProvider
     */
    public function testIsArray($parameter, $expect = false): void
    {
        $this->assertSame(
            ExtendedArray::isArray($parameter),
            $expect
        );
    }

    /**
     * Is Array Provider
     */
    public function isArrayProvider(): array
    {
        $this->setUp();

        return [
            'array-plain' => ['parameter' => $this->plainArray, 'expect' => true],
            'array-extended' => ['parameter' => $this->extendedArray, 'expect' => true],
            'array-iterator' => ['parameter' => $this->arrayIterator, 'expect' => true],
            'array-object' => ['parameter' => $this->arrayObject, 'expect' => true],
            'array-spl-fixed' => ['parameter' => $this->splFixedArray, 'expect' => true],
            'fail-non-array-null' => ['parameter' => null],
            'fail-non-array-bool' => ['parameter' => false],
            'fail-non-array-object' => ['parameter' => $this],
            'fail-non-array-int' => ['parameter' => 1024],
            'fail-non-array-serial' => ['parameter' => $this->extendedArray->serialize()],
            'fail-non-array-json' => ['parameter' => $this->extendedArray->jsonSerialize()],
        ];
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
     *
     * @dataProvider mapProvider
     */
    public function testMap($empty, $callable, $secondArray = null): void
    {
        $plainArray = $empty ? [] : $this->plainArray;
        $extendedArray = $empty ? $this->emptyArray : $this->extendedArray;

        $extendedArray->next();
        next($plainArray);

        $arrayMap = $secondArray
            ? array_map($callable, $plainArray, $secondArray)
            : array_map($callable, $plainArray);
        $extendedArrayMap = $secondArray
            ? $extendedArray->map($callable, $secondArray)->getArrayCopy()
            : $extendedArray->map($callable)->getArrayCopy();

        $this->assertSame($arrayMap, $extendedArrayMap);
        $this->assertSame(key($plainArray), $extendedArray->key());
    }

    /**
     * Map Provider
     */
    public function mapProvider(): array
    {
        return [
            'strlen' => [
                'empty' => false,
                'callable' => function ($item) {
                    if (is_array($item)) {
                        $item = json_encode($item);
                    }
                    return strlen($item);
                },
            ],
            'string-cast' => [
                'empty' => false,
                'callable' => function ($item) {
                    return is_array($item) ? json_encode($item) : (string) $item;
                },
            ],
            'cube' => [
                'empty' => false,
                'callable' => function ($item) {
                    if (ExtendedArray::isArray($item)) {
                        $item = (new ExtendedArray($item))->count();
                    }
                    if (!is_int($item)) {
                        $item = intval($item);
                    }
                    return $item * $item * $item;
                },
            ],
            'is-array' => [
                'empty' => false,
                'callable' => function ($item) {
                    return ExtendedArray::isArray($item);
                },
            ],
            'empty-true' => [
                'empty' => true,
                'callable' => function ($item) {
                    return $item;
                },
            ],
            'second-incomplete' => [
                'empty' => false,
                'callable' => function ($item, $color) {
                    $asString = is_array($item) ? json_encode($item) : (string) $item;
                    return "Color: {$color}; Item: {$asString};";
                },
                'secondArray' => [
                    'one' => 'red',
                    'green',
                    7 => 'blue',
                ],
            ],
        ];
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
     * Test MapWithObjects
     *
     * @dataProvider mapWithObjectsProvider
     */
    public function testMapWithObjects($empty, $callable, $secondArray = null): void
    {
        $plainArray = $empty ? [] : $this->plainArray;
        $extendedArray = $empty ? $this->emptyArray : $this->extendedArray;

        $extendedArray->next();
        next($plainArray);

        $arrayMap = $secondArray
            ? array_map($callable, $plainArray, $secondArray)
            : array_map($callable, $plainArray);
        $extendedArrayMap = $secondArray
            ? $extendedArray->mapWithObjects($callable, $secondArray)->getArrayCopy()
            : $extendedArray->mapWithObjects($callable)->getArrayCopy();

        $this->assertSame($arrayMap, $extendedArrayMap);
        $this->assertSame(key($plainArray), $extendedArray->key());
    }

    /**
     * Map With Objects Provider
     */
    public function mapWithObjectsProvider(): array
    {
        return array_merge(
            $this->mapProvider(),
            [
                'method-contains' => [
                    'empty' => false,
                    'callable' => function ($item) {
                        if (!ExtendedArray::isArray($item)) {
                            return false;
                        }
                        return is_array($item)
                            ? !!array_search('two', $item)
                            : $item->contains('two');
                    }
                ],
            ]
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

            $this->assertTrue(false); // Hasn't thrown an exception
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
     * @test Sorting ElementArray takes less than 50ms
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
