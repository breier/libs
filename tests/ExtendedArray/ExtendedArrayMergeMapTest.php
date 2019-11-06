<?php
/**
 * Class ExtendedArrayMergeMapTest
 *
 * PHP version 7
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/phpunit/phpunit/phpunit tests/ExtendedArrayMergeMapTest.php
 */

namespace Test\ExtendedArray;

use Breier\ExtendedArray\ExtendedArrayMergeMap;
use Breier\ExtendedArray\ExtendedArray;
use PHPUnit\Framework\TestCase;

/**
 * Class ExtendedArrayMergeMapTest
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/phpunit/phpunit/phpunit tests/ExtendedArrayMergeMapTest.php
 */
class ExtendedArrayMergeMapTest extends TestCase
{
    protected $plainArray;
    protected $extendedArray;

    /**
     * Set up an example array for every test
     *
     * @return null
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
     *
     * @return null
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
     *
     * @return null
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
     *
     * @return null
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
     *
     * @return null
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
     *
     * @return null
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
            $null = ExtendedArrayMergeMap::prepareMapParams(
                $this->extendedArray,
                [1, null]
            );
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
