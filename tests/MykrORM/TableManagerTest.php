<?php

/**
 * Table Manager Test File
 *
 * PHP version 7
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/bin/phpunit tests/MykrORM/TableManagerTest.php
 */

namespace Test\MykrORM;

use PHPUnit\Framework\TestCase;
use Test\MykrORM\MykrORMTestModel;
use Breier\MykrORM\Exception\DBException;

/**
 * Table Manager Test Class
 */
class TableManagerTest extends TestCase
{
    private $testModel;

    /**
     * Set up an example DB Model for every test
     */
    public function setUp(): void
    {
        $this->testModel = new MykrORMTestModel();
    }

    /**
     * Test Create Table If Not Exists
     */
    public function testCreateTableIfNotExists(): void
    {
        $this->testModel->exposedCreateTableIfNotExists();

        // Try again with empty table (can't detect changes on empty table)
        $this->testModel->exposedCreateTableIfNotExists();

        $this->testModel->setText('first creation test');
        $this->testModel->create();

        // Try again with data but no alteration
        $this->testModel->exposedCreateTableIfNotExists();

        // Try altering the table (add extra column)
        $this->testModel->insertExtraIntoDBProperties('TEXT');

        $this->testModel->exposedCreateTableIfNotExists();
        $this->testModel->setExtra('extra update');
        $this->testModel->update(['id' => 1]);

        $newModelResponse = MykrORMTestModel::find(['id' => 1]);
        $newModel = $newModelResponse->current();
        $newModel->insertExtraIntoDBProperties('TEXT');

        $this->assertSame(
            $newModel->getExtra(),
            $this->testModel->getExtra()
        );

        @unlink(__DIR__ . '/../../testing.sqlite3');
    }

    /**
     * Test Fail Create Table If Not Exists
     */
    public function testFailCreateTableIfNotExists(): void
    {
        // Fail create
        try {
            $this->testModel->insertExtraIntoDBProperties('{:?+=²}¹£¢');
            $this->testModel->exposedCreateTableIfNotExists();

            $this->assertTrue(false); // Hasn't thrown an exception
        } catch (DBException $e) {
            $this->assertSame(
                'SQLSTATE[HY000]: General error: 1 unrecognized token: "{"',
                $e->getMessage()
            );
        }

        @unlink(__DIR__ . '/../../testing.sqlite3');

        // Fail alter
        $this->testModel = new MykrORMTestModel();
        $this->testModel->exposedCreateTableIfNotExists();
        $this->testModel->setText('first creation test');
        $this->testModel->create();
        try {
            $this->testModel->insertExtraIntoDBProperties('{:?+=²}¹£¢');
            $this->testModel->exposedCreateTableIfNotExists();

            $this->assertTrue(false); // Hasn't thrown an exception
        } catch (DBException $e) {
            $this->assertSame(
                'SQLSTATE[HY000]: General error: 1 unrecognized token: "{"',
                $e->getMessage()
            );
        }

        @unlink(__DIR__ . '/../../testing.sqlite3');
    }
}
