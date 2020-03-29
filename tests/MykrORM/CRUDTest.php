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

use PHPUnit\Framework\TestCase;
use Test\MykrORM\MykrORMTestModel;
use Breier\ExtendedArray\ExtendedArray;
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
        $this->testModel->destroyDB();
        $this->testModel = new MykrORMTestModel();

        $this->testModel->setText('success creation');
        $this->testModel->create();

        $newModelList = $this->testModel->find(['id' => 1]);
        $newModel = $newModelList->current();

        $this->assertSame(
            $newModel->getText(),
            $this->testModel->getText()
        );
    }

    /**
     * Test Fail Create Entry in the table
     */
    public function testCreateFailure(): void
    {
        $this->testModel->destroyDB();
        $this->testModel = new MykrORMTestModel();

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
     * Test Find Multiple Entries
     */
    public function testFindMultiple(): void
    {
        $this->testModel->destroyDB();
        $this->testModel = new MykrORMTestModel();

        $this->testModel->setText('same text');
        $this->testModel->create();
        $this->testModel->create();

        $newModelList = $this->testModel->find(['text' => 'same text']);

        $this->assertSame(2, $newModelList->count());
        $this->assertEquals(1, $newModelList->first()->element()->getId());
        $this->assertEquals(2, $newModelList->next()->element()->getId());
    }

    /**
     * Test Fail Find Entry in the table
     *
     * @dataProvider findProvider
     */
    public function testFailFind($criteria, $expect, $exec = null): void
    {
        $this->testModel->destroyDB();
        $this->testModel = new MykrORMTestModel();

        $this->testModel->setDate('2020-03-22 17:16:15');
        $this->testModel->setText('success creation');
        $this->testModel->create();

        if (!empty($exec)) {
            eval($exec); // dangerous uh 8)
        }

        if ($expect instanceof MykrORMTestModel || empty($expect['exception'])) {
            $newModelList = $this->testModel->find($criteria);
            $newModel = $newModelList->current();

            $this->assertEquals($newModel, $expect);
        } else {
            try {
                $this->testModel->find($criteria);

                $this->assertTrue(false); // Hasn't thrown an exception
            } catch (DBException $e) {
                $this->assertSame($expect['exception'], $e->getMessage());
            }
        }
    }

    /**
     * Find Provider
     */
    public function findProvider(): array
    {
        $emptyTestModel = new MykrORMTestModel();
        $emptyTestModel->setDate('2020-03-22 17:16:15');
        $emptyTestModel->setText('success creation');
        $emptyTestModel->setId('1');

        return [
            'not-there' => [
                'criteria' => ['id' => 5],
                'expect' => null,
            ],
            'extended-not-there' => [
                'criteria' => new ExtendedArray(['id' => 5]),
                'expect' => null,
            ],
            'empty' => [
                'criteria' => [],
                'expect' => $emptyTestModel,
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
                'criteria' => ['extra_prop' => 123],
                'expect' => ['exception' => "Invalid criteria 'extra_prop'!"],
            ],
            'no-db' => [
                'criteria' => ['id' => 123],
                'expect' => [
                    'exception' => 'SQLSTATE[HY000]: General error: 1 no such table: mykrormtestmodel'
                ],
                'exec' => '$this->testModel->destroyDB();'
                    . ' $this->testModel = new \Test\MykrORM\MykrORMTestModel();',
            ],
        ];
    }

    /**
     * Test Update Entry in the table
     *
     * @dataProvider updateProvider
     */
    public function testUpdate($criteria, $update, $expect, $exec = null): void
    {
        $this->testModel->destroyDB();
        $this->testModel = new MykrORMTestModel();

        foreach ($update as $setter => $value) {
            if (!empty($value['create'])) {
                $value = $value['create'];
            }
            $this->testModel->{$setter}($value);
        }
        $this->testModel->create();

        if (!empty($exec)) {
            eval($exec); // dangerous uh 8)
        }

        foreach ($update as $setter => $value) {
            if (!empty($value['update'])) {
                $value = $value['update'];
            }
            $this->testModel->{$setter}($value);
        }

        if (empty($expect['exception'])) {
            $this->testModel->update($criteria);

            $newModelList = $this->testModel->find($criteria);
            $newModel = $newModelList->current();

            $this->assertEquals(
                $newModel->getProperties()->getArrayCopy(),
                $expect
            );
        } else {
            try {
                $this->testModel->update($criteria);

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
     * Update Provider
     */
    public function updateProvider(): array
    {
        return [
            'success-one' => [
                'criteria' => ['id' => 1],
                'update' => [
                    'setDate' => '2020-03-22 17:16:15',
                ],
                'expect' => [
                    'id' => '1',
                    'date' => '2020-03-22 17:16:15',
                ],
            ],
            'success-two' => [
                'criteria' => ['id' => 2],
                'update' => [
                    'setId' => 2,
                    'setDate' => '2020-03-22 17:16:15',
                ],
                'expect' => [
                    'id' => '2',
                    'date' => '2020-03-22 17:16:15',
                ],
            ],
            'success-three' => [
                'criteria' => ['id' => 3],
                'update' => [
                    'setId' => 3,
                    'setDate' => '2020-03-22 17:16:15',
                    'setText' => ['create' => 'nope', 'update' => 'testing text'],
                ],
                'expect' => [
                    'id' => '3',
                    'date' => '2020-03-22 17:16:15',
                    'text' => 'testing text',
                ],
            ],
            'fail-table' => [
                'criteria' => ['id' => 4],
                'update' => [
                    'setId' => 3,
                    'setDate' => '2020-03-22 17:16:15',
                ],
                'expect' => ['exception' => 'Test\MykrORM\MykrORMTestModel Not Found!'],
                'exec' => '$this->testModel->destroyDB();'
                    . ' $this->testModel = new \Test\MykrORM\MykrORMTestModel();',
            ],
            'fail-empty' => [
                'criteria' => [],
                'update' => [
                    'setId' => 3,
                    'setDate' => '2020-03-22 17:16:15',
                ],
                'expect' => ['exception' => 'Test\MykrORM\MykrORMTestModel Not Found!'],
            ],
            'fail-criteria' => [
                'criteria' => ['id' => 5],
                'update' => [
                    'setId' => 3,
                    'setDate' => '2020-03-22 17:16:15',
                ],
                'expect' => ['exception' => '\'{"id":5}\' Not Found!'],
            ],
            'fail-data' => [
                'criteria' => ['id' => 6],
                'update' => [
                    'setId' => ['create' => 6, 'update' => 'nonsense'],
                    'setDate' => '2020-03-22 17:16:15',
                ],
                'expect' => [
                    'exception' => 'SQLSTATE[HY000]: General error: 20 datatype mismatch'
                ],
            ],
        ];
    }

    /**
     * Test Delete Entry in the table
     */
    public function testDelete(): void
    {
        $this->testModel->setText('to be deleted');
        $this->testModel->create();

        try {
            $this->testModel->delete();

            $this->assertTrue(false); // Hasn't thrown an exception
        } catch (DBException $e) {
            $this->assertSame(
                "'id' is empty!",
                $e->getMessage()
            );
        }

        $newModelList = $this->testModel->find(['text' => 'to be deleted']);
        $newModel = $newModelList->current();
        $newModel->delete();

        $newModelList = $this->testModel->find(['text' => 'to be deleted']);
        $this->assertNull($newModelList->current());

        try {
            $newModel->delete();

            $this->assertTrue(false); // Hasn't thrown an exception
        } catch (DBException $e) {
            $this->assertSame(
                "'id' was not found or unique!",
                $e->getMessage()
            );
        }
    }
}
