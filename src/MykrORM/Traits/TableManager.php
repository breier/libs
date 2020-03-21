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
        } catch (PDOException $e) {
            $this->createTable();
            return;
        }

        $fields = $result->fetch();
        if ($fields === false) {
            return;
        }

        $this->alterTable(
            $this->getDBProperties()->keys()->diff(array_keys($fields))
        );
    }

    /**
     * Create this table
     *
     * @throws DBException
     */
    private function createTable(): void
    {
        $fields = $this->getDBProperties()->map(
            function ($type, $field) {
                return "{$field} {$type}";
            },
            $this->getDBProperties()->keys()->getArrayCopy()
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
        if ($diff->count() === 0) {
            return;
        }

        foreach ($diff as $field) {
            try {
                $this->getConnection()->exec(
                    "ALTER TABLE {$this->dbTableName} ADD {$field} "
                    . $this->getDBProperties()->offsetGet($field)
                );
            } catch (PDOException $e) {
                throw new DBException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}
