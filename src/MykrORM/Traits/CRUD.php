<?php

/**
 * PHP Version 7
 *
 * CRUD Trait File
 *
 * @category Extended_Class
 * @package  Breier\Libs
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Breier\MykrORM\Traits;

use PDO;
use PDOStatement;
use PDOException;
use Breier\ExtendedArray\ExtendedArray;
use Breier\MykrORM\Exception\DBException;

/**
 * CRUD Trait class
 */
trait CRUD
{
    /**
     * [Create] Insert new row to this table
     *
     * @throws DBException
     */
    public function create(): void
    {
        $this->createTableIfNotExists();

        $parameters = $this->getProperties()->filter(
            function ($item) {
                return !is_null($item);
            }
        );

        $placeholders = ExtendedArray::fill(0, $parameters->count(), '?');

        $query = "INSERT INTO {$this->dbTableName}"
            . " ({$parameters->keys()->implode(', ')}) VALUES"
            . " ({$placeholders->implode(', ')})";

        $this->getConnection()->beginTransaction();
        try {
            $preparedStatement = $this->getConnection()->prepare($query);
            $this->bindIndexedParams($preparedStatement, $parameters);
            $preparedStatement->execute();
            $this->getConnection()->commit();
        } catch (PDOException $e) {
            $this->getConnection()->rollBack();
            throw new DBException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * [Read] Get Existing Entry
     *
     * @param array|ExtendedArray $criteria
     *
     * @throws DBException
     */
    public function find($criteria): ExtendedArray
    {
        $this->validateCriteria($criteria);
        $criteria = new ExtendedArray($criteria);

        $whereClause = '';
        if ($criteria->count()) {
            $placeholders = $criteria->keys()->map(
                function ($field) {
                    $property = static::camelToSnake($field);
                    return "{$property} = ?";
                }
            )->implode(' AND ');

            $whereClause = " WHERE {$placeholders}";
        }

        try {
            $preparedStatement = $this->getConnection()->prepare(
                "SELECT * FROM {$this->dbTableName}{$whereClause}"
            );
            $this->bindIndexedParams($preparedStatement, $criteria);
            $preparedStatement->execute();

            $result = new ExtendedArray();
            while (
                $row = $preparedStatement->fetchObject(
                    static::class,
                    $this->dbConstructorArgs
                )
            ) {
                $result->append($row);
            }

            return $result;
        } catch (PDOException $e) {
            throw new DBException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * [Update] Change One Entry
     *
     * @param array|ExtendedArray $criteria
     *
     * @throws DBException
     */
    public function update($criteria): void
    {
        try {
            $this->validateCriteria($criteria);

            $criteria = new ExtendedArray($criteria);
            if ($criteria->count() === 0) {
                throw new DBException('');
            }

            $originalList = $this->find($criteria);
        } catch (DBException $e) {
            throw new DBException(static::class . ' Not Found!');
        }

        if ($originalList->count() !== 1) {
            $jsonCriteria = (new ExtendedArray($criteria))->jsonSerialize();
            throw new DBException("'{$jsonCriteria}' Not Found!");
        }
        $original = $originalList->first()->element();

        $parameters = $this->getProperties();
        $placeholders = $parameters->keys()->map(
            function ($field) {
                return "{$field} = ?";
            }
        );

        $primaryField = $this->findPrimaryKey();
        $primaryGetter = 'get' . static::snakeToCamel($primaryField);
        $parameters->append($original->{$primaryGetter}());

        $query = "UPDATE {$this->dbTableName}"
            . " SET {$placeholders->implode(', ')}"
            . " WHERE {$primaryField} = ?";

        $this->getConnection()->beginTransaction();
        try {
            $preparedStatement = $this->getConnection()->prepare($query);
            $this->bindIndexedParams($preparedStatement, $parameters);
            $preparedStatement->execute();
            $this->getConnection()->commit();
        } catch (PDOException $e) {
            $this->getConnection()->rollBack();
            throw new DBException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * [Delete] Erase Current Entry
     *
     * @throws DBException
     */
    public function delete(): void
    {
        $primaryField = $this->findPrimaryKey();
        $primaryGetter = 'get' . static::snakeToCamel($primaryField);
        $primaryValue = $this->{$primaryGetter}();
        if (empty($primaryValue)) {
            throw new DBException("'{$primaryField}' is empty!");
        }

        $query = "DELETE FROM {$this->dbTableName} WHERE {$primaryField} = ?";

        $this->getConnection()->beginTransaction();
        try {
            $preparedStatement = $this->getConnection()->prepare($query);
            $preparedStatement->execute([$primaryValue]);
            if ($preparedStatement->rowCount() !== 1) {
                throw new PDOException(
                    "'{$primaryField}' was not found or unique!"
                );
            }
            $this->getConnection()->commit();
        } catch (PDOException $e) {
            $this->getConnection()->rollBack();
            throw new DBException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Validate Criteria
     *
     * @param array|ExtendedArray $criteria
     *
     * @throws DBException
     */
    public function validateCriteria($criteria): bool
    {
        if (!ExtendedArray::isArray($criteria)) {
            throw new DBException('Invalid criteria format!');
        }

        $criteria = new ExtendedArray($criteria);

        foreach ($criteria->keys() as $field) {
            $property = static::camelToSnake($field);
            if (!$this->getDBProperties()->offsetExists($property)) {
                throw new DBException("Invalid criteria '{$field}'!");
            }
        }

        return true;
    }

    /**
     * Prepare Fields for insertion
     */
    protected function getProperties(): ExtendedArray
    {
        $propertyFields = new ExtendedArray($this->getDBProperties());

        foreach ($propertyFields as $field => &$value) {
            $getter = 'get' . static::snakeToCamel($field);
            $value = $this->{$getter}();
        }

        return $propertyFields;
    }

    /**
     * Bind Statement Parameters using dynamic PDO types
     */
    protected function bindIndexedParams(
        PDOStatement $statement,
        ExtendedArray $parameters
    ): void {
        $index = 0;

        foreach ($parameters as &$value) {
            if (is_null($value)) {
                $PDOParamType = PDO::PARAM_NULL;
            } elseif (is_bool($value)) {
                $PDOParamType = PDO::PARAM_BOOL;
            } else {
                $PDOParamType = PDO::PARAM_STR;
            }

            $statement->bindParam(++$index, $value, $PDOParamType);
        }
    }

    /**
     * Find Primary Key
     */
    protected function findPrimaryKey(): string
    {
        foreach ($this->getDBProperties() as $field => $type) {
            if (preg_match('/PRIMARY KEY/', strtoupper($type)) === 1) {
                return $field;
            }
        }

        return $this->getDBProperties()->keys()->first()->element();
    }
}
