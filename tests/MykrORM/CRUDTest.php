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

        $newModelList = MykrORMTestModel::find(['text' => 'success creation']);
        $newModel = $newModelList->current();

        $this->assertSame(
            $newModel->getText(),
            $this->testModel->getText()
        );
    }
}
