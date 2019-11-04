<?php
/**
 * Class ExtendedArrayBaseTest
 *
 * PHP version 7
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/phpunit/phpunit/phpunit tests/ExtendedArrayBaseTest.php
 */

namespace Test\ExtendedArray;

use Breier\ExtendedArray\ExtendedArrayBase;
use PHPUnit\Framework\TestCase;

use Breier\ExtendedArray\ExtendedArrayException;

use ArrayIterator;
use ArrayObject;
use SplFixedArray;

/**
 * Class ExtendedArrayBaseClass
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/phpunit/phpunit/phpunit tests/ExtendedArrayBaseTest.php
 */
class ExtendedArrayBaseClass extends ExtendedArrayBase
{
    /**
     * Get Keys
     *
     * @return ExtendedArrayBaseClass
     */
    public function keys(): ExtendedArrayBaseClass
    {
        return new ExtendedArrayBaseClass($this->getPositionMap());
    }
}

/**
 * Class ExtendedArrayBaseTest
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/phpunit/phpunit/phpunit tests/ExtendedArrayBaseTest.php
 */
class ExtendedArrayBaseTest extends TestCase
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
                'temp' => 'long string that\'s not so long',
                'empty' => null
            ],
        ];

        $this->extendedArray = new ExtendedArrayBaseClass($this->plainArray);
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
         * Instantiated with ArrayIterator(array)
         */
        $this->assertSame(
            $this->plainArray,
            (new ExtendedArrayBaseClass($this->arrayIterator))->getArrayCopy()
        );

        /**
         * Instantiated with ArrayObject(array)
         */
        $this->assertSame(
            $this->plainArray,
            (new ExtendedArrayBaseClass($this->arrayObject))->getArrayCopy()
        );

        /**
         * Instantiated empty, then populated by unserialize
         */
        $fromSerialized = new ExtendedArrayBaseClass();
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
            (new ExtendedArrayBaseClass($this->splFixedArray))->getArrayCopy()
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
        $jsonStringifiedArray['six'] = json_decode(
            $jsonStringifiedArray['six'],
            true
        );
        $jsonStringifiedArray[0] = json_decode(
            $jsonStringifiedArray[0],
            true
        );
        $this->extendedArray->natcasesort();
        $this->assertSame(
            $jsonStringifiedArray,
            $this->extendedArray->getArrayCopy()
        );
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
        $jsonStringifiedArray['six'] = json_decode(
            $jsonStringifiedArray['six'],
            true
        );
        $jsonStringifiedArray[0] = json_decode(
            $jsonStringifiedArray[0],
            true
        );
        $this->extendedArray->natsort();
        $this->assertSame(
            $jsonStringifiedArray,
            $this->extendedArray->getArrayCopy()
        );
        $this->assertSame(
            array_keys($jsonStringifiedArray),
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
            $this->plainArray['simple']->toArray(),
            $this->extendedArray->simple->getArrayCopy()
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
            $this->plainArray['set']->toArray(),
            $this->extendedArray->offsetGet('set')->getArrayCopy()
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
     * Test Is Array Object works with all array objects
     *
     * @return null
     * @test   Is Array Object works with all array objects
     */
    public function isArrayObjectWorksWithAllArrayObjects(): void
    {
        $this->assertTrue(
            ExtendedArrayBaseClass::isArrayObject($this->extendedArray)
        );
        $this->assertTrue(
            ExtendedArrayBaseClass::isArrayObject($this->arrayIterator)
        );
        $this->assertTrue(
            ExtendedArrayBaseClass::isArrayObject($this->arrayObject)
        );

        $this->assertFalse(ExtendedArrayBaseClass::isArrayObject(null));
        $this->assertFalse(ExtendedArrayBaseClass::isArrayObject(false));
        $this->assertFalse(ExtendedArrayBaseClass::isArrayObject($this));
        $this->assertFalse(ExtendedArrayBaseClass::isArrayObject(1024));
        $this->assertFalse(
            ExtendedArrayBaseClass::isArrayObject(
                $this->extendedArray->serialize()
            )
        );
        $this->assertFalse(
            ExtendedArrayBaseClass::isArrayObject(
                $this->extendedArray->jsonSerialize()
            )
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
        $this->extendedArray->ksort();
        $this->extendedArray->natsort();
        $this->extendedArray->ksort();
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
}
