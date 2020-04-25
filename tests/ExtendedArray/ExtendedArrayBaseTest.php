<?php

/**
 * Extended Array Base Test File
 *
 * PHP version 7
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/bin/phpunit tests/ExtendedArray/ExtendedArrayBaseTest.php
 */

namespace Test\ExtendedArray;

use Breier\ExtendedArray\ExtendedArray;
use PHPUnit\Framework\TestCase;
use ArrayIterator;
use ArrayObject;
use SplFixedArray;
use InvalidArgumentException;

/**
 * Extended Array Base Test Class
 */
class ExtendedArrayBaseTest extends TestCase
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
     * Test Instantiate
     *
     * @dataProvider instanceProvider
     */
    public function testInstantiate($instance, $exception = false): void
    {
        try {
            $extendedArray = new ExtendedArray($instance);

            if ($exception) {
                $this->assertTrue(false); // Hasn't thrown an exception
            }
        } catch (InvalidArgumentException $e) {
            $this->assertSame(
                'Only array types are accepted as parameter!',
                $e->getMessage()
            );
            return;
        }

        $plainArray = $instance instanceof SplFixedArray
            ? array_values($this->plainArray)
            : $this->plainArray;

        $extendedArray->next();
        next($plainArray);

        $this->assertSame(
            $plainArray,
            $extendedArray->getArrayCopy()
        );
        $this->assertSame(
            array_keys($plainArray),
            $extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($plainArray),
            $extendedArray->key()
        );
    }

    /**
     * Instantiate Provider
     */
    public function instanceProvider(): array
    {
        $this->setUp();

        return [
            'array' => ['instance' => $this->extendedArray],
            'array-iterator' => ['instance' => $this->arrayIterator],
            'array-object' => ['instance' => $this->arrayObject],
            'array-spl-fixed' => ['instance' => $this->splFixedArray],
            'fail-non-array' => ['instance' => 'non-array', 'exception' => true],
            'fail-non-array-int' => ['instance' => 123, 'exception' => true],
            'fail-non-array-bool' => ['instance' => true, 'exception' => true],
            'fail-non-array-float' => ['instance' => 123.456789, 'exception' => true],
            'fail-non-array-object' => ['instance' => (object) ['invalid'], 'exception' => true],
        ];
    }

    /**
     * Test toString
     */
    public function testToString(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $magicStringFromObject = sprintf('%s', $this->extendedArray);
        $this->assertSame(
            $magicStringFromObject,
            $this->extendedArray->jsonSerialize()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame('{}', sprintf('%s', $this->emptyArray));
    }

    /**
     * Test Append [indirect]
     */
    public function testAppend(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->extendedArray->append('appended element');
        array_push($this->plainArray, 'appended element');
 
        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->getArrayCopy()
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
     * Test Asort
     */
    public function testAsort(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        asort($this->plainArray);

        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->asort()->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame([], $this->emptyArray->asort()->getArrayCopy());
    }

    /**
     * Test Element
     */
    public function testElement(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            current($this->plainArray),
            $this->extendedArray->element()->getArrayCopy()
        );

        $this->assertSame(null, $this->emptyArray->element());
    }

    /**
     * Test End
     */
    public function testEnd(): void
    {
        end($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->end()->key()
        );

        $this->assertSame(null, $this->emptyArray->end()->key());
    }

    /**
     * Test First
     */
    public function testFirst(): void
    {
        reset($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->first()->key()
        );

        $this->assertSame(null, $this->emptyArray->first()->key());
    }

    /**
     * Test GetArrayCopy
     *
     * It's pretty much covered everywhere else
     *
     * So I'm just skipping it ;)
     */

    /**
     * Test Is Array Object
     *
     * @dataProvider isArrayProvider
     */
    public function testIsArrayObject($parameter, $expect = false): void
    {
        $this->assertSame(
            ExtendedArray::isArrayObject($parameter),
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
            'array-plain' => ['parameter' => $this->plainArray],
            'array-extended' => ['parameter' => $this->extendedArray, 'expect' => true],
            'array-iterator' => ['parameter' => $this->arrayIterator, 'expect' => true],
            'array-object' => ['parameter' => $this->arrayObject, 'expect' => true],
            'array-spl-fixed' => ['parameter' => $this->splFixedArray],
            'fail-non-array-null' => ['parameter' => null],
            'fail-non-array-bool' => ['parameter' => false],
            'fail-non-array-object' => ['parameter' => $this],
            'fail-non-array-int' => ['parameter' => 1024],
            'fail-non-array-serial' => ['parameter' => $this->extendedArray->serialize()],
            'fail-non-array-json' => ['parameter' => $this->extendedArray->jsonSerialize()],
        ];
    }

    /**
     * Test Json Serialize
     */
    public function testJsonSerialize(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $plainArrayJSON = json_encode($this->plainArray);
        $extendedArrayJSON = $this->extendedArray->jsonSerialize();
        $this->assertSame(
            $plainArrayJSON,
            $extendedArrayJSON
        );

        $plainArrayPrettyJSON = json_encode(
            $this->plainArray,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
        $extendedArrayPrettyJSON = $this->extendedArray->jsonSerialize(
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
        $this->assertSame(
            $plainArrayPrettyJSON,
            $extendedArrayPrettyJSON
        );

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame('{}', $this->emptyArray->jsonSerialize());
    }

    /**
     * Test Ksort
     */
    public function testKsort(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        ksort($this->plainArray);

        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->ksort()->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame([], $this->emptyArray->ksort()->getArrayCopy());
    }

    /**
     * Test Last
     */
    public function testLast(): void
    {
        end($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->last()->key()
        );

        $this->assertSame(null, $this->emptyArray->last()->key());
    }

    /**
     * Test NatCaseSort
     */
    public function testNatCaseSort(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->plainArray[0] = json_encode($this->plainArray[0]);
        $this->plainArray['six'] = json_encode($this->plainArray['six']);
        natcasesort($this->plainArray);
        $this->plainArray['six'] = json_decode($this->plainArray['six'], true);
        $this->plainArray[0] = json_decode($this->plainArray[0], true);

        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->natcasesort()->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame([], $this->emptyArray->natcasesort()->getArrayCopy());
    }

    /**
     * Test NatSort
     */
    public function testNatSort(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->plainArray[0] = json_encode($this->plainArray[0]);
        $this->plainArray['six'] = json_encode($this->plainArray['six']);
        natsort($this->plainArray);
        $this->plainArray['six'] = json_decode($this->plainArray['six'], true);
        $this->plainArray[0] = json_decode($this->plainArray[0], true);

        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->natsort()->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame([], $this->emptyArray->natsort()->getArrayCopy());
    }

    /**
     * Test Next
     */
    public function testNext(): void
    {
        next($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->next()->key()
        );

        end($this->plainArray);
        next($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->end()->next()->key()
        );

        $this->assertSame(null, $this->emptyArray->next()->key());
    }

    /**
     * Test OffsetExists
     */
    public function testOffsetExists(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            array_key_exists('one', $this->plainArray),
            $this->extendedArray->offsetExists('one')
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame(
            array_key_exists(5, $this->plainArray),
            $this->extendedArray->offsetExists(5)
        );
        $this->assertSame(
            array_key_exists(null, $this->plainArray),
            $this->extendedArray->offsetExists(null)
        );
        $this->assertSame(
            array_key_exists('one', []),
            $this->emptyArray->offsetExists('one')
        );
    }

    /**
     * Test OffsetSet
     */
    public function testOffsetSet(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        /**
         * Overwriting as property
         */
        $this->extendedArray->{7} = 'seven';
        $this->plainArray[7] = 'seven';
        $this->assertSame(
            $this->plainArray[7],
            $this->extendedArray->{7}
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->extendedArray->next();
        next($this->plainArray);

        /**
         * Adding as property
         */
        $this->extendedArray->simple = $this->splFixedArray;
        $this->plainArray['simple'] = $this->splFixedArray;
        $this->assertSame(
            $this->plainArray['simple']->toArray(),
            $this->extendedArray->simple->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->extendedArray->next();
        next($this->plainArray);

        /**
         * Overwriting with OffsetSet
         */
        $this->extendedArray->offsetSet(8, 'eight');
        $this->plainArray[8] = 'eight';
        $this->assertSame(
            $this->plainArray[8],
            $this->extendedArray->offsetGet(8)
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->extendedArray->next();
        next($this->plainArray);

        /**
         * Adding with OffsetSet
         */
        $this->extendedArray->offsetSet('set', $this->splFixedArray);
        $this->plainArray['set'] = $this->splFixedArray;
        $this->assertSame(
            $this->plainArray['set']->toArray(),
            $this->extendedArray->offsetGet('set')->getArrayCopy()
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
     * Test OffsetUnset
     */
    public function testOffsetUnset(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        /**
         * Unset as property
         */
        unset($this->plainArray['six']);
        unset($this->extendedArray->six);
        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->extendedArray->next();
        next($this->plainArray);

        /**
         * Unset with OffsetUnset
         */
        unset($this->plainArray[7]);
        $this->extendedArray->offsetUnset(7);
        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->getArrayCopy()
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
     * Test Prev
     */
    public function testPrev(): void
    {
        end($this->plainArray);
        prev($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->end()->prev()->key()
        );

        reset($this->plainArray);
        prev($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->first()->prev()->key()
        );

        $this->assertSame(null, $this->emptyArray->prev()->key());
    }

    /**
     * Test Rewind
     */
    public function testRewind(): void
    {
        reset($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->rewind()->key()
        );

        $this->assertSame(null, $this->emptyArray->rewind()->key());
    }

    /**
     * Test Uasort
     */
    public function testUasort(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->extendedArray->uasort(
            function ($a, $b) {
                return md5(json_encode($a)) <=> md5(json_encode($b));
            }
        );
        uasort(
            $this->plainArray,
            function ($a, $b) {
                return md5(json_encode($a)) <=> md5(json_encode($b));
            }
        );

        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame(
            [],
            $this->emptyArray->uasort(
                function ($a, $b) {
                    return md5(json_encode($a)) <=> md5(json_encode($b));
                }
            )->getArrayCopy()
        );
    }

    /**
     * Test Uksort
     */
    public function testUksort(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->extendedArray->uksort(
            function ($a, $b) {
                return md5(json_encode($a)) <=> md5(json_encode($b));
            }
        );
        uksort(
            $this->plainArray,
            function ($a, $b) {
                return md5(json_encode($a)) <=> md5(json_encode($b));
            }
        );

        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame(
            [],
            $this->emptyArray->uksort(
                function ($a, $b) {
                    return md5(json_encode($a)) <=> md5(json_encode($b));
                }
            )->getArrayCopy()
        );
    }

    /**
     * @test Sorting ElementArray takes less than 50ms
     */
    public function executionTimeIsAcceptable(): void
    {
        $startTime = microtime(true);

        $this->extendedArray->asort();
        $this->extendedArray->ksort();
        $this->extendedArray->natsort();
        $this->extendedArray->ksort();
        $this->extendedArray->natcasesort();

        $timeTaken = microtime(true) - $startTime;

        $this->assertLessThan(0.05, $timeTaken);
    }
}
