<?php

/**
 * CRUD Test File
 *
 * PHP version 7
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     php vendor/bin/phpunit tests/MykrORM/CRUDTest.php
 */

namespace Test\MykrORM;

use Breier\ExtendedArray\ExtendedArray;
use PHPUnit\Framework\TestCase;
use Test\MykrORM\MykrORMTestModel;
use Breier\MykrORM\Exception\DBException;

/**
 * CRUD Test Class
 */
class CRUDTest extends TestCase
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
     * Test Create Entry in the table
     */
    public function testCreateSuccess(): void
    {
        $this->testModel->setText('success creation');
        $this->testModel->create();

        $newModelList = MykrORMTestModel::find(['id' => 1]);
        $newModel = $newModelList->current();

        $this->assertSame(
            $newModel->getText(),
            $this->testModel->getText()
        );

        $this->testModel->destroyDB();
    }

    /**
     * Test Fail Create Entry in the table
     */
    public function testCreateFailure(): void
    {
        $this->testModel->setId('somethingElse');

        try {
            $this->testModel->create();

            $this->assertTrue(false); // Hasn't thrown an exception
        } catch (DBException $e) {
            $this->assertSame(
                'SQLSTATE[HY000]: General error: 20 datatype mismatch',
                $e->getMessage()
            );
        }
    }

    /**
     * Test Fail Find Entry in the table
     *
     * @dataProvider findProvider
     */
    public function testFailFind($criteria, $expect, $exec = null): void
    {
        if (!empty($exec)) {
            eval($exec); // dangerous uh 8)
        }

        if (empty($expect['exception'])) {
            $newModelList = MykrORMTestModel::find($criteria);
            $newModel = $newModelList->current();

            $this->assertSame(
                $newModel,
                $expect
            );
        } else {
            try {
                MykrORMTestModel::find($criteria);

                $this->assertTrue(false); // Hasn't thrown an exception
            } catch (DBException $e) {
                $this->assertSame(
                    $expect['exception'],
                    $e->getMessage()
                );
            }
        }
    }

    /**
     * Find Provider
     */
    public function findProvider(): array
    {
        return [
            'not-there' => [
                'criteria' => ['id' => 1],
                'expect' => null,
            ],
            'extended-not-there' => [
                'criteria' => new ExtendedArray(['id' => 1]),
                'expect' => null,
            ],
            'empty' => [
                'criteria' => [],
                'expect' => ['exception' => 'Invalid criteria!'],
            ],
            'not-array' => [
                'criteria' => 'not-array',
                'expect' => ['exception' => 'Invalid criteria format!'],
            ],
            'not-property' => [
                'criteria' => ['not-property' => 123],
                'expect' => ['exception' => "Invalid criteria 'not-property'!"],
            ],
            'not-db-property' => [
                'criteria' => ['extra' => 123],
                'expect' => ['exception' => "Invalid criteria 'extra'!"],
            ],
            'no-db' => [
                'criteria' => ['id' => 123],
                'expect' => [
                    'exception' => 'SQLSTATE[HY000]: General error: 1 no such table: mykrormtestmodel'
                ],
                'exec' => '$this->testModel->destroyDB();',
            ],
        ];
    }
}
