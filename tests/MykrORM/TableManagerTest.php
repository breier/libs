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

        // Try altering the table (add extra_prop column)
        $this->testModel->insertExtraIntoDBProperties('TEXT');

        $this->testModel->exposedCreateTableIfNotExists();
        $this->testModel->setExtraProp('extra property update');
        $this->testModel->update(['id' => 1]);

        $newModelList = MykrORMTestModel::find(['id' => 1]);
        $newModel = $newModelList->current();
        $newModel->insertExtraIntoDBProperties('TEXT');

        $this->assertSame(
            $newModel->getExtraProp(),
            $this->testModel->getExtraProp()
        );

        $this->testModel->destroyDB();
    }

    /**
     * Test Fail Create Table If Not Exists
     */
    public function testFailCreateTableIfNotExists(): void
    {
        // Fail empty
        $this->testModel->emptyDBProperties();
        try {
            $this->testModel->exposedCreateTableIfNotExists();

            $this->assertTrue(false); // Hasn't thrown an exception
        } catch (DBException $e) {
            $this->assertSame(
                'Empty DB properties!',
                $e->getMessage()
            );
        }

        // Fail create
        $this->testModel = new MykrORMTestModel();
        $this->testModel->insertExtraIntoDBProperties('{:?+=²}¹£¢');
        try {
            $this->testModel->exposedCreateTableIfNotExists();

            $this->assertTrue(false); // Hasn't thrown an exception
        } catch (DBException $e) {
            $this->assertSame(
                'SQLSTATE[HY000]: General error: 1 unrecognized token: "{"',
                $e->getMessage()
            );
        }
        $this->testModel->destroyDB();

        // Fail alter
        $this->testModel = new MykrORMTestModel();
        $this->testModel->exposedCreateTableIfNotExists();
        $this->testModel->setText('first creation test');
        $this->testModel->create();

        $this->testModel->insertExtraIntoDBProperties('{:?+=²}¹£¢');
        try {
            $this->testModel->exposedCreateTableIfNotExists();

            $this->assertTrue(false); // Hasn't thrown an exception
        } catch (DBException $e) {
            $this->assertSame(
                'SQLSTATE[HY000]: General error: 1 unrecognized token: "{"',
                $e->getMessage()
            );
        }
        $this->testModel->destroyDB();
    }
}
