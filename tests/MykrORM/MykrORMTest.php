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
use Breier\MykrORM\Exception\UndefinedMethodException;
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
        $this->assertTrue($this->testModel->getTestConn() instanceof PDO);

        // Successful Stored Connection
        $this->assertTrue($this->testModel->getTestConn() instanceof PDO);
    }

    /**
     * Test Fail Connection
     */
    public function testFailConnection(): void
    {
        try {
            $this->testModel->testDSN = 'invalid';
            $fail = $this->testModel->getTestConn();
        } catch (DBException $e) {
            $this->assertSame(
                'invalid data source name',
                $e->getMessage()
            );
        }

        try {
            $this->testModel->testDSN = 'mysql:user=not;dbname=exists';
            $fail = $this->testModel->getTestConn();
        } catch (DBException $e) {
            $this->assertSame(
                'SQLSTATE[HY000] [2002] No such file or directory',
                $e->getMessage()
            );
        }
    }

    /**
     * Test Auto Getters (__call)
     *
     * Setters are defined by MykrORMTestModel
     */
    public function testAutoGetters(): void
    {
        $this->testModel->setId(123);
        $this->assertSame(123, $this->testModel->getId());

        $this->testModel->setDate('2020-03-21 15:20:25');
        $this->assertSame('2020-03-21 15:20:25', $this->testModel->getDate());

        $this->testModel->setText('anything shorter then 256');
        $this->assertSame('anything shorter then 256', $this->testModel->getText());

        try {
            $this->testModel->setExtra(321);
            $fail = $this->testModel->getExtra();
        } catch (DBException $e) {
            $this->assertSame(
                'Property is not DB property!',
                $e->getMessage()
            );
        }

        try {
            $fail = $this->testModel->getNonExistent();
        } catch (DBException $e) {
            $this->assertSame(
                'Property does not exist!',
                $e->getMessage()
            );
        }

        try {
            $fail = $this->testModel->callUndefined();
        } catch (UndefinedMethodException $e) {
            $this->assertSame(
                'Attempted to call an undefined method named "callUndefined"'
                    . ' of class "{Test\MykrORM\MykrORMTestModel}".',
                $e->getMessage()
            );
        }
    }

    /**
     * Test __set
     *
     * It's only covered by PDO::fetchObject() @ CRUD
     *
     * So I'm just skipping it here
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
