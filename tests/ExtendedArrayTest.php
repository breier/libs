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

namespace Test;

use PHPUnit\Framework\TestCase;
use TypeError;
use Breier\ExtendedArray;

/**
 * Class ExtendedArrayTest
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/phpunit/phpunit/phpunit tests/ExtendedArrayTest.php
 */
class ElementArrayTest extends TestCase
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
        $this->plainArray = [
            'one' => 1,
            [2 => 'two', 'three'],
            7 => 'four',
            'five'
        ];

        $this->extendedArray = new ExtendedArray($this->plainArray);
    }

    /**
     * Test constants have expected values
     *
     * @test   constants have expected values
     * @return null
     */
    public function constantsAreSetAsExpected(): void
    {
        $this->assertSame(1, ElementArray::RANDOM_FIRST);

        $this->assertSame(500, ElementArray::RANDOM_LAST);
    }

    /**
     * Test throw TypeError Exception for non-arrays
     *
     * @test   throw TypeError Exception for non-arrays
     * @return null
     */
    public function throwsForNonArrays(): void
    {
        $this->expectException(TypeError::class);

        ElementArray::getMissingValues("Definitely not an Array ... =P");
    }

    /**
     * Test returned the correct array
     *
     * @test   returned the correct array
     * @return null
     */
    public function returnsCorrectArray(): void
    {
        $result = ElementArray::process();

        $this->assertArrayHasKey("incomplete_array", $result);
        $this->assertArrayHasKey("missing_values", $result);
    }

    /**
     * Test returned the correct incomplete_array as JSON
     *
     * @test   returned the correct incomplete_array as JSON
     * @return null
     */
    public function returnedIncompleteArrayIsJSON(): void
    {
        $result = ElementArray::process();

        try {
            $incompleteArray = json_decode($result["incomplete_array"], true);
        } catch (\Exception|\Throwable $e) {
            $incompleteArray = "Definitely not an Array ... =P";
        }

        $this->assertTrue(is_array($incompleteArray));
    }

    /**
     * Test returned the correct missing_values as string
     *
     * @test   returned the correct missing_values as string
     * @return null
     */
    public function returnedMissingValuesIsString(): void
    {
        $result = ElementArray::process();

        $this->assertTrue(is_string($result["missing_values"]));
    }

    /**
     * Test returned the correct missing value
     *
     * @test   returned the correct missing value
     * @return null
     */
    public function returnsCorrectMissingValue(): void
    {
        $baseArray = ElementArray::getBaseArray();

        $result = ElementArray::process();

        $incompleteArray = json_decode($result["incomplete_array"], true);

        $missingValues = [];

        foreach ($baseArray as $value) {
            if (array_search($value, $incompleteArray) === false) {
                array_push($missingValues, $value);
            }
        }

        $this->assertSame(implode(", ", $missingValues), $result["missing_values"]);
    }

    /**
     * Test 1000 ElementArray takes less than 50ms
     *
     * @test   1000 ElementArray takes less than 50ms
     * @return null
     */
    public function executionTimeIsAcceptable(): void
    {
        $startTime = microtime(true);
        ElementArray::process();
        $timeTaken = microtime(true) - $startTime;

        $this->assertLessThan(0.05, $timeTaken);
    }
}
