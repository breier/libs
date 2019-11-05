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
     * @return array
     */
    public function keys(): array
    {
        return $this->getPositionMap();
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
        $this->splFixedArray = SplFixedArray::fromArray(
            array_values($this->plainArray)
        );
    }

    /**
     * Test Instantiate Array
     *
     * @return null
     */
    public function testInstantiateArray(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            $this->plainArray,
            $this->extendedArray->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Instantiate ArrayIterator
     *
     * @return null
     */
    public function testInstantiateArrayIterator(): void
    {
        $newFromArrayIterator = new ExtendedArrayBaseClass($this->arrayIterator);
        $newFromArrayIterator->next();
        next($this->plainArray);

        $this->assertSame(
            $this->plainArray,
            $newFromArrayIterator->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $newFromArrayIterator->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $newFromArrayIterator->key()
        );
    }

    /**
     * Test Instantiate ArrayObject
     *
     * @return null
     */
    public function testInstantiateArrayObject(): void
    {
        $newFromArrayObject = new ExtendedArrayBaseClass($this->arrayObject);
        $newFromArrayObject->next();
        next($this->plainArray);

        $this->assertSame(
            $this->plainArray,
            $newFromArrayObject->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $newFromArrayObject->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $newFromArrayObject->key()
        );
    }

    /**
     * Test Instantiate Serialized
     *
     * @return null
     */
    public function testInstantiateSerialized(): void
    {
        $newFromSerialized = new ExtendedArrayBaseClass();
        $newFromSerialized->unserialize(
            $this->extendedArray->serialize()
        );
        $newFromSerialized->next();
        next($this->plainArray);

        $this->assertSame(
            $this->plainArray,
            $newFromSerialized->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $newFromSerialized->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $newFromSerialized->key()
        );
    }

    /**
     * Test Instantiate SplFixedArray
     *
     * @return null
     */
    public function testInstantiateSplFixedArray(): void
    {
        $newFromSplFixedArray = new ExtendedArrayBaseClass($this->splFixedArray);
        $newFromSplFixedArray->next();
        $this->splFixedArray->next();

        $this->assertSame(
            $this->splFixedArray->toArray(),
            $newFromSplFixedArray->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->splFixedArray->toArray()),
            $newFromSplFixedArray->keys()
        );
        $this->assertSame(
            $this->splFixedArray->key(),
            $newFromSplFixedArray->key()
        );
    }

    /**
     * Test toString
     *
     * @return null
     */
    public function testToString(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $magicStringFromObject = sprintf("%s", $this->extendedArray);
        $this->assertSame(
            $magicStringFromObject,
            $this->extendedArray->jsonSerialize()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Append [indirect]
     *
     * @return null
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
            $this->extendedArray->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Asort
     *
     * @return null
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
            $this->extendedArray->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Element
     *
     * @return null
     */
    public function testElement(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            current($this->plainArray),
            $this->extendedArray->element()->getArrayCopy()
        );
    }

    /**
     * Test End
     *
     * @return null
     */
    public function testEnd(): void
    {
        end($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->end()->key()
        );
    }

    /**
     * Test First
     *
     * @return null
     */
    public function testFirst(): void
    {
        reset($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->first()->key()
        );
    }

    /**
     * Test Is Array Object
     *
     * @return null
     */
    public function testIsArrayObject(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertTrue(is_array($this->plainArray));
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

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Json Serialize
     *
     * @return null
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
    }

    /**
     * Test Ksort
     *
     * @return null
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
            $this->extendedArray->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Last
     *
     * @return null
     */
    public function testLast(): void
    {
        end($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->last()->key()
        );
    }

    /**
     * Test NatCaseSort
     *
     * @return null
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
            $this->extendedArray->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test NarSort
     *
     * @return null
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
            $this->extendedArray->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Next
     *
     * @return null
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
    }

    /**
     * Test OffsetSet
     *
     * @return null
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
            $this->extendedArray->keys()
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
            $this->extendedArray->keys()
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
            $this->extendedArray->keys()
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
            $this->extendedArray->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test OffsetUnset
     *
     * @return null
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
            $this->extendedArray->keys()
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
            $this->extendedArray->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Prev
     *
     * @return null
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
    }

    /**
     * Test Rewind
     *
     * @return null
     */
    public function testRewind(): void
    {
        reset($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->rewind()->key()
        );
    }

    /**
     * Test Uasort
     *
     * @return null
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
            $this->extendedArray->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Uksort
     *
     * @return null
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
            $this->extendedArray->keys()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
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
}
