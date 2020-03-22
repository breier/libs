<?php

/**
 * Extended Array Merge Map Test File
 *
 * PHP version 7
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/bin/phpunit tests/ExtendedArray/ExtendedArrayMergeMapTest.php
 */

namespace Test\ExtendedArray;

use PHPUnit\Framework\TestCase;
use Breier\ExtendedArray\{ExtendedArray, ExtendedArrayMergeMap};

/**
 * Extended Array Merge Map Test Class
 */
class ExtendedArrayMergeMapTest extends TestCase
{
    private $plainArray;
    private $extendedArray;

    /**
     * Set up an example array for every test
     */
    public function setUp(): void
    {
        $this->extendedArray = new ExtendedArray(
            [
                'Ireland' => 'Dublin',
                'France' => 'Paris',
                'Egypt' => 'Cairo',
                'Japan' => 'Tokyo',
            ]
        );

        $this->plainArray = [
            'util' => 'Umbrella',
            'food' => 'Croisant',
            'must' => 'Pyramids',
            'tree' => 'Sakura'
        ];
    }

    /**
     * Test Instantiate
     */
    public function testInstantiate(): void
    {
        $mapEmpty = new ExtendedArrayMergeMap();
        $this->assertSame(
            [],
            $mapEmpty->getArrayCopy()
        );

        $mapOne = new ExtendedArrayMergeMap('one');
        $this->assertSame(
            ['one'],
            $mapOne->getArrayCopy()
        );

        $mapTwo = new ExtendedArrayMergeMap(2, ['three', 4 => 'five']);
        $this->assertSame(
            [2, ['three', 4 => 'five']],
            $mapTwo->getArrayCopy()
        );
    }

    /**
     * Test GetArrayCopy
     */
    public function testGetArrayCopy(): void
    {
        $mapWithObjects = new ExtendedArrayMergeMap(
            ['sub-array' => true],
            $this->extendedArray
        );
        
        $this->assertSame(
            [
                ['sub-array' => true],
                $this->extendedArray
            ],
            $mapWithObjects->getArrayCopy()
        );

        $emptyMap = new ExtendedArrayMergeMap();
        $this->assertSame([], $emptyMap->getArrayCopy());
    }

    /**
     * Test Merge
     */
    public function testMerge(): void
    {
        $mapWithObjects = new ExtendedArrayMergeMap('non-sub-array');
        $mapWithObjects->merge(['sub-array' => true])
            ->merge($this->extendedArray)
            ->merge($this->extendedArray->Japan);

        $this->assertSame(
            [
                'non-sub-array',
                ['sub-array' => true],
                $this->extendedArray,
                $this->extendedArray->Japan
            ],
            $mapWithObjects->getArrayCopy()
        );
    }

    /**
     * Test MergePush [static]
     */
    public function testMergePush(): void
    {
        ExtendedArrayMergeMap::mergePush($this->extendedArray, $this->plainArray);

        $this->assertSame(
            [
                'Ireland' => ['Dublin', 'Umbrella'],
                'France' => ['Paris', 'Croisant'],
                'Egypt' => ['Cairo', 'Pyramids'],
                'Japan' => ['Tokyo', 'Sakura']
            ],
            $this->extendedArray->getArrayCopy()
        );

        ExtendedArrayMergeMap::mergePush($this->extendedArray, [5, 3.5, 2]);

        $this->assertSame(
            [
                'Ireland' => ['Dublin', 'Umbrella', 5],
                'France' => ['Paris', 'Croisant', 3.5],
                'Egypt' => ['Cairo', 'Pyramids', 2],
                'Japan' => ['Tokyo', 'Sakura', null]
            ],
            $this->extendedArray->getArrayCopy()
        );
    }

    /**
     * Test PrepareMapParams [static]
     */
    public function testPrepareMapParams(): void
    {
        /**
         * Full Array
         */
        $mapParams = ExtendedArrayMergeMap::prepareMapParams(
            $this->extendedArray,
            [$this->plainArray, [5, 3.5, null, 200]]
        );

        $this->assertSame(
            [
                ['Dublin', 'Umbrella', 5],
                ['Paris', 'Croisant', 3.5],
                ['Cairo', 'Pyramids', null],
                ['Tokyo', 'Sakura', 200]
            ],
            $mapParams->getArrayCopy()
        );

        /**
         * Half Array
         */
        $mapParams = ExtendedArrayMergeMap::prepareMapParams(
            $this->extendedArray,
            [$this->plainArray]
        );

        $this->assertSame(
            [
                ['Dublin', 'Umbrella'],
                ['Paris', 'Croisant'],
                ['Cairo', 'Pyramids'],
                ['Tokyo', 'Sakura']
            ],
            $mapParams->getArrayCopy()
        );

        /**
         * Empty Array
         */
        $mapParams = ExtendedArrayMergeMap::prepareMapParams($this->extendedArray);

        $this->assertSame(
            [
                ['Dublin'],
                ['Paris'],
                ['Cairo'],
                ['Tokyo']
            ],
            $mapParams->getArrayCopy()
        );

        /**
         * Throws InvalidArgumentException
         */
        try {
            ExtendedArrayMergeMap::prepareMapParams(
                $this->extendedArray,
                [1, null]
            );

            $this->assertTrue(false); // Hasn't thrown an exception
        } catch (\InvalidArgumentException $e) {
            $this->assertSame(
                'Second parameter has to be an array of arrays!',
                $e->getMessage()
            );
        }

        /**
         * Original Array should be untouched
         */
        $this->assertSame(
            [
                'Ireland' => 'Dublin',
                'France' => 'Paris',
                'Egypt' => 'Cairo',
                'Japan' => 'Tokyo'
            ],
            $this->extendedArray->getArrayCopy()
        );
    }
}
