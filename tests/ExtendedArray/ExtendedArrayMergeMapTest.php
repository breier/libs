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
        $this->extendedArray = new ExtendedArray(
            [
                'Ireland' => 'Dublin',
                'France' => 'Paris',
                'Egypt' => 'Cairo',
                'Japan' => 'Tokyo',
            ]
        );
    }

    /**
     * Test instantiate new, merge and get elements
     *
     * @return null
     * @test   instantiate new, merge and get elements
     */
    public function newMergeAndGet(): void
    {
        $mapOne = new ExtendedArrayMergeMap('one');
        $mapOne->merge('two');

        $mapTwo = new ExtendedArrayMergeMap('one', 'two');

        $this->assertSame($mapOne->getElements(), $mapTwo->getElements());
    }
}
