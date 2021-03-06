<?php

/**
 * MykrORM Test File
 *
 * PHP version 7
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/bin/phpunit tests/MykrORM/MykrORMTest.php
 */

namespace Test\MykrORM;

use PHPUnit\Framework\TestCase;
use Test\MykrORM\MykrORMTestModel;
use Breier\MykrORM\MykrORM;
use Breier\MykrORM\Exception\DBException;
use PDO;

/**
 * MykrORM Test Class
 */
class MykrORMTest extends TestCase
{
    private $testModel;

    /**
     * Set up an example DB Model for every test
     */
    public function setUp(): void
    {
        $this->testModel = new MykrORMTestModel('MykrORMTestTable');
        $this->testModel->testDSN = 'sqlite::memory:';
    }

    /**
     * Test Instantiation
     */
    public function testInstantiation(): void
    {
        // Use setUp table name
        $this->assertTrue($this->testModel instanceof MykrORM);

        // Use automatic table name
        $this->testModel = new MykrORMTestModel();
        $this->assertTrue($this->testModel instanceof MykrORM);
    }

    /**
     * Test Get Connection
     */
    public function testGetConnection(): void
    {
        // Successful New Connection
        $this->assertTrue($this->testModel->exposedGetConnection() instanceof PDO);

        // Successful Stored Connection
        $this->assertTrue($this->testModel->exposedGetConnection() instanceof PDO);
    }

    /**
     * Test Fail Connection
     */
    public function testFailConnection(): void
    {
        try {
            $this->testModel->testDSN = 'invalid';
            $this->testModel->exposedGetConnection();

            $this->assertTrue(false); // Hasn't thrown an exception
        } catch (DBException $e) {
            $this->assertSame(
                'invalid data source name',
                $e->getMessage()
            );
        }

        try {
            $this->testModel->testDSN = 'mysql:user=not;dbname=exists';
            $this->testModel->exposedGetConnection();

            $this->assertTrue(false); // Hasn't thrown an exception
        } catch (DBException $e) {
            $this->assertSame(
                'SQLSTATE[HY000] [2002] No such file or directory',
                $e->getMessage()
            );
        }
    }

    /**
     * Test Auto Getters / Setters (__get, __set)
     *
     * Setters are defined by MykrORMTestModel
     *
     * @dataProvider getterSetterProvider
     */
    public function testAutoGetters($field, $value = null, $exception = null): void
    {
        try {
            if (null !== $value) {
                $this->testModel->{$field} = $value;
            }

            $result = $this->testModel->{$field};

            if (null === $exception) {
                $this->assertSame($value, $result);
            } else {
                $this->assertTrue(false); // Hasn't thrown an exception
            }
        } catch (DBException $e) {
            $this->assertSame($exception, $e->getMessage());
        }
    }

    /**
     * Getter Setter Provider
     */
    public function getterSetterProvider(): array
    {
        return [
            'success-set-id' => [
                'field' => 'id',
                'value' => 123,
            ],
            'success-set-date' => [
                'field' => 'date',
                'value' => '2020-03-21 15:20:25',
            ],
            'success-set-text' => [
                'field' => 'text',
                'value' => 'anything shorter then 256',
            ],
            'fail-set-non-db' => [
                'field' => 'extraProp',
                'value' => 321,
                'exception' => 'Property is not DB property!',
            ],
            'fail-set-non-existent' => [
                'field' => 'nonExistent',
                'value' => 'nope',
                'exception' => 'Property does not exist!',
            ],
            'fail-get-non-db' => [
                'field' => 'extraProp',
                'value' => null,
                'exception' => 'Property is not DB property!',
            ],
            'fail-get-non-existent' => [
                'field' => 'nonExistent',
                'value' => null,
                'exception' => 'Property does not exist!',
            ],
        ];
    }

    /**
     * Test __set
     *
     * It's covered by PDO::fetchObject() @ CRUD::find
     * by setting a composed word property at the model:
     * 'extra_prop' => 'setExtraProp()' / '$extraProp'
     */

    /**
     * Test Snake To Camel case
     */
    public function testSnakeToCamel(): void
    {
        $this->assertSame(
            'BigWordsAreEasyToCover',
            $this->testModel->snake2camel('big_words_are_easy_to_cover')
        );

        $this->assertSame(
            'BigWordsWithCAPSWorkToo',
            $this->testModel->snake2camel('big_words_with_CAPS_work_too')
        );

        $this->assertSame(
            'WordsWith123Numbers',
            $this->testModel->snake2camel('words_with_123_numbers')
        );
    }

    /**
     * Test Camel To Snake case
     */
    public function testCamelToSnake(): void
    {
        $this->assertSame(
            'big_words_are_easy_to_cover',
            $this->testModel->camel2snake('BigWordsAreEasyToCover')
        );

        $this->assertSame(
            'big_words_with_caps_go_lower',
            $this->testModel->camel2snake('BigWordsWithCAPSGoLower')
        );

        $this->assertSame(
            'words_with_123_numbers',
            $this->testModel->camel2snake('WordsWith123Numbers')
        );
    }
}
