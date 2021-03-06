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

        $this->testModel->text = 'success creation';
        $this->testModel->create();

        $newModelList = $this->testModel->find(['id' => 1]);
        $newModel = $newModelList->current();

        $this->assertSame(
            $newModel->text,
            $this->testModel->text
        );
    }

    /**
     * Test Fail Create Entry in the table
     */
    public function testCreateFailure(): void
    {
        $this->testModel->destroyDB();
        $this->testModel = new MykrORMTestModel();

        $this->testModel->id = 'somethingElse';

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

        $this->testModel->text = 'same text';
        $this->testModel->create();
        $this->testModel->create();

        $newModelList = $this->testModel->find(['text' => 'same text']);

        $this->assertSame(2, $newModelList->count());
        $this->assertEquals(1, $newModelList->first()->element()->id);
        $this->assertEquals(2, $newModelList->next()->element()->id);
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

        $this->testModel->date = '2020-03-22 17:16:15';
        $this->testModel->text = 'success creation';
        $this->testModel->create();

        if (!empty($exec)) {
            eval($exec); // dangerous uh 8)
        }

        if ($expect instanceof MykrORMTestModel || empty($expect['exception'])) {
            $newModelList = $this->testModel->find($criteria);
            $newModel = $newModelList->current();

            if ($newModel) { // initializes DB properties
                $newModel->exposedGetProperties();
            }

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
        $emptyTestModel->date = '2020-03-22 17:16:15';
        $emptyTestModel->text = 'success creation';
        $emptyTestModel->id = '1';

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
    public function testUpdate($model, $criteria, $update, $expect, $exec = null): void
    {
        $this->testModel->destroyDB();
        $this->testModel = new $model();

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
                $newModel->exposedGetProperties()->getArrayCopy(),
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
                'model' => '\Test\MykrORM\MykrORMTestModel',
                'criteria' => ['id' => 1],
                'update' => [
                    'setId' => 1,
                    'setDate' => '2020-03-22 17:16:15',
                ],
                'expect' => [
                    'id' => '1',
                    'date' => '2020-03-22 17:16:15',
                    'text' => null,
                ],
            ],
            'success-two' => [
                'model' => '\Test\MykrORM\MykrORMTestModel',
                'criteria' => ['id' => 2],
                'update' => [
                    'setId' => 2,
                    'setDate' => '2020-03-22 17:16:15',
                ],
                'expect' => [
                    'id' => '2',
                    'date' => '2020-03-22 17:16:15',
                    'text' => null,
                ],
            ],
            'success-three' => [
                'model' => '\Test\MykrORM\MykrORMTestModel',
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
            'success-false' => [
                'model' => '\Test\MykrORM\MykrORMTestFalseModel',
                'criteria' => ['name' => 1],
                'update' => [
                    'setName' => 1,
                    'setIsValid' => false,
                ],
                'expect' => [
                    'name' => '1',
                    'is_valid' => false,
                ],
            ],
            'fail-table' => [
                'model' => '\Test\MykrORM\MykrORMTestModel',
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
                'model' => '\Test\MykrORM\MykrORMTestModel',
                'criteria' => [],
                'update' => [
                    'setId' => 3,
                    'setDate' => '2020-03-22 17:16:15',
                ],
                'expect' => ['exception' => 'Criteria cannot be empty!'],
            ],
            'fail-criteria' => [
                'model' => '\Test\MykrORM\MykrORMTestModel',
                'criteria' => ['id' => 5],
                'update' => [
                    'setId' => 3,
                    'setDate' => '2020-03-22 17:16:15',
                ],
                'expect' => ['exception' => '\'{"id":5}\' Not Found!'],
            ],
            'fail-data' => [
                'model' => '\Test\MykrORM\MykrORMTestModel',
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
        $this->testModel->text = 'to be deleted';
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
