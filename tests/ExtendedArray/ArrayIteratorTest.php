<?php

/**
 * Array Iterator Test File
 *
 * PHP version 7
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/phpunit/phpunit/phpunit tests/ArrayIteratorTest.php
 */

namespace Test\ExtendedArray;

use PHPUnit\Framework\TestCase;

use Breier\ExtendedArray\ExtendedArray;
use ArrayIterator;

/**
 * Array Iterator Test Class
 */
class ArrayIteratorTest extends TestCase
{
    private $emptyArray;
    private $plainArray;
    private $extendedArray;

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
    }

    /**
     * Test Count
     */
    public function testCount(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            count($this->plainArray),
            $this->extendedArray->count()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $this->extendedArray->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame(0, $this->emptyArray->count());
    }

    /**
     * Test Current
     */
    public function testCurrent(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            current($this->plainArray),
            $this->extendedArray->current()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame(null, $this->emptyArray->current());
    }

    /**
     * Test Get Flags ExtendedArray
     */
    public function testGetFlagsExtendedArray(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            ArrayIterator::ARRAY_AS_PROPS,
            $this->extendedArray->getFlags()
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame(
            ArrayIterator::ARRAY_AS_PROPS,
            $this->emptyArray->getFlags()
        );
    }

    /**
     * Test Get Flags ArrayIterator
     */
    public function testGetFlagsArrayIterator(): void
    {
        $arrayIterator = new ArrayIterator($this->plainArray);

        $this->assertSame(0, $arrayIterator->getFlags());
        $this->assertFalse(isset($arrayIterator->one));
        $this->assertTrue(isset($arrayIterator['one']));
    }

    /**
     * Test Key
     */
    public function testKey(): void
    {
        for (
            $this->extendedArray->first();
            $this->extendedArray->valid();
            $this->extendedArray->next()
        ) {
            $this->assertSame(
                key($this->plainArray),
                $this->extendedArray->key()
            );

            next($this->plainArray);
        }

        next($this->plainArray);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame(null, $this->emptyArray->key());
    }

    /**
     * Test Offset Get
     */
    public function testOffsetGet(): void
    {
        $this->extendedArray->next();
        next($this->plainArray);

        $this->assertSame(
            $this->plainArray[8],
            $this->extendedArray->offsetGet(8)
        );
        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        $this->assertSame(
            $this->plainArray['six'],
            $this->extendedArray->offsetGet('six')->getArrayCopy()
        );
    }

    /**
     * Test Seek
     */
    public function testSeek(): void
    {
        next($this->plainArray);
        $this->extendedArray->seek(1);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        next($this->plainArray);
        $this->extendedArray->seek(2);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        end($this->plainArray);
        $this->extendedArray->seek(4);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );

        reset($this->plainArray);
        $this->extendedArray->seek(0);

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Serialize
     */
    public function testSerialize(): void
    {
        $simplifiedSerialized = str_replace(
            'Breier\ExtendedArray\ExtendedArray',
            '',
            $this->extendedArray->serialize()
        );

        similar_text(
            serialize($this->plainArray),
            $simplifiedSerialized,
            $similarity
        );

        $this->assertGreaterThan(47, $similarity);
    }

    /**
     * Test Set Flags
     */
    public function testSetFlags(): void
    {
        next($this->plainArray);
        $this->extendedArray->next();

        $this->assertSame(
            ArrayIterator::ARRAY_AS_PROPS,
            $this->extendedArray->getFlags()
        );
        $this->assertTrue(isset($this->extendedArray->one));
        $this->assertTrue(isset($this->extendedArray['one']));

        $this->extendedArray->setFlags(ArrayIterator::STD_PROP_LIST);
        $this->assertSame(
            ArrayIterator::STD_PROP_LIST,
            $this->extendedArray->getFlags()
        );
        $this->assertFalse(isset($this->extendedArray->one));
        $this->assertTrue(isset($this->extendedArray['one']));

        $this->extendedArray->setFlags(0);
        $this->assertSame(
            0,
            $this->extendedArray->getFlags()
        );
        $this->assertFalse(isset($this->extendedArray->one));
        $this->assertTrue(isset($this->extendedArray['one']));

        $this->extendedArray->setFlags(ArrayIterator::ARRAY_AS_PROPS);
        $this->assertSame(
            ArrayIterator::ARRAY_AS_PROPS,
            $this->extendedArray->getFlags()
        );
        $this->assertTrue(isset($this->extendedArray->one));
        $this->assertTrue(isset($this->extendedArray['one']));

        $this->assertSame(
            key($this->plainArray),
            $this->extendedArray->key()
        );
    }

    /**
     * Test Unserialize
     */
    public function testUnserialize(): void
    {
        $unSerialized = new ExtendedArray();
        $unSerialized->unserialize(
            $this->extendedArray->serialize()
        );
        $unSerialized->next();
        next($this->plainArray);

        $this->assertSame(
            $this->plainArray,
            $unSerialized->getArrayCopy()
        );
        $this->assertSame(
            array_keys($this->plainArray),
            $unSerialized->keys()->getArrayCopy()
        );
        $this->assertSame(
            key($this->plainArray),
            $unSerialized->key()
        );
    }

    /**
     * Test Valid
     *
     * It's pretty much covered everywhere else
     *
     * So I'm just skipping it ;)
     */
}
