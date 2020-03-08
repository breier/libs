<?php

/**
 * PHP Version 7
 *
 * Table Manager Trait File
 *
 * @category Extended_Class
 * @package  Breier\Libs
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Breier\MykrORM\Traits;

use PDOException;
use Breier\ExtendedArray\ExtendedArray;
use Breier\MykrORM\Exception\DBException;

/**
 * Table Manager Trait class
 */
trait TableManager
{
    /**
     * Create this table if it doesn't exist
     * And "alter" it if necessary
     *
     * @throws DBException
     */
    protected function createTableIfNotExists(): void
    {
        try {
            $result = $this->getConnection()->query(
                "SELECT * FROM {$this->dbTableName} LIMIT 1"
            );

            $diffProperties = $this->dbProperties->keys()->diff(
                array_keys($result->fetch())
            );
            if ($diffProperties->count()) {
                $this->alterTable($diffProperties);
            }
        } catch (PDOException $e) {
            $this->createTable();
        }
    }

    /**
     * Create this table
     *
     * @throws DBException
     */
    private function createTable(): void
    {
        $fields = $this->dbProperties->map(
            function ($type, $field) {
                return "{$field} {$type}";
            },
            $this->dbProperties->keys()->getArrayCopy()
        )->implode(', ');

        try {
            $this->getConnection()->exec(
                "CREATE TABLE {$this->dbTableName} ({$fields})"
            );
        } catch (PDOException $e) {
            throw new DBException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Alter this table
     *
     * @throws DBException
     */
    private function alterTable(ExtendedArray $diff): void
    {
        // @TODO: check and update table fields in DB
    }
}
