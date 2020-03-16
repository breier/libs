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
        if (empty($this->dbProperties) || !ExtendedArray::isArray($this->dbProperties)) {
            throw new DBException("Invalid DB properties!");
        }

        $this->createTableIfNotExists();

        $parameters = $this->getProperties();
        $placeholders = ExtendedArray::fill(0, $parameters->count(), '?');

        $query = "INSERT INTO {$this->dbTableName}"
            . " ({$parameters->keys()->implode(', ')}) VALUES"
            . " ({$placeholders->implode(', ')})";

        $this->getConnection()->beginTransaction();
        try {
            $preparedStatement = $this->getConnection()->prepare($query);
            $preparedStatement->execute($parameters->values()->getArrayCopy());
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
    public static function find($criteria): ExtendedArray
    {
        self::validateCriteria($criteria);
        $criteria = new ExtendedArray($criteria);

        $placeholders = $criteria->map(
            function ($value, $field) {
                $property = static::camelToSnake($field);
                $operator = is_array($value) ? 'IN' : '=';
                return "{$property} {$operator} :{$field}";
            },
            $criteria->keys()->getArrayCopy()
        )->implode(' AND ');
        
        try {
            $model = new static();

            $preparedStatement = $model->getConnection()->prepare(
                "SELECT * FROM {$model->dbTableName} WHERE {$placeholders}"
            );
            $preparedStatement->execute($criteria->getArrayCopy());

            $result = new ExtendedArray();
            while ($row = $preparedStatement->fetchObject(static::class)) {
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
        $original = static::find($criteria);
        if ($original->count() !== 1) {
            throw new DBException(static::class . ' Not Found!');
        }
        $original = $original->first()->element();

        $parameters = $this->getProperties();
        $placeholders = $parameters->keys()->map(
            function ($field) {
                return "{$field} = ?";
            }
        );

        $model = new static();
        $firstField = $model->dbProperties->keys()->first()->element();
        $firstGetter = 'get' . static::snakeToCamel($firstField);
        $parameters->append($original->{$firstGetter}());

        $query = "UPDATE {$model->dbTableName}"
            . " SET {$placeholders->implode(', ')}"
            . " WHERE {$firstField} = ?";

        $this->getConnection()->beginTransaction();
        try {
            $preparedStatement = $this->getConnection()->prepare($query);
            $preparedStatement->execute($parameters->values()->getArrayCopy());
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
        $model = new static();
        $firstField = $model->dbProperties->keys()->first()->element();
        $firstGetter = 'get' . static::snakeToCamel($firstField);
        $parameter = [$this->{$firstGetter}()];

        $query = "DELETE FROM {$model->dbTableName} WHERE {$firstField} = ?";

        $this->getConnection()->beginTransaction();
        try {
            $preparedStatement = $this->getConnection()->prepare($query);
            $preparedStatement->execute($parameter);
            if ($preparedStatement->rowCount() !== 1) {
                throw new PDOException(static::class . ' Not Found!');
            }
            $this->getConnection()->commit();
        } catch (PDOException $e) {
            $this->getConnection()->rollBack();
            throw new DBException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Prepare Fields for insertion
     */
    public function getProperties(): ExtendedArray
    {
        $allowedFields = $this->dbProperties->filter(
            function ($type) {
                return stristr($type, 'SERIAL') === false
                    && stristr($type, 'INCREMENT') === false;
            }
        );

        foreach ($allowedFields as $field => &$value) {
            $getter = 'get' . static::snakeToCamel($field);
            $value = $this->{$getter}();
        }

        return $allowedFields->filter();
    }

    /**
     * Validate Criteria
     *
     * @param array|ExtendedArray $criteria
     *
     * @throws DBException
     */
    public static function validateCriteria($criteria): bool
    {
        if (!ExtendedArray::isArray($criteria)) {
            throw new DBException("Invalid criteria format!");
        }

        $criteria = new ExtendedArray($criteria);
        if (!$criteria->count()) {
            throw new DBException("Invalid criteria!");
        }

        $model = new static();

        foreach ($criteria->keys() as $field) {
            $property = static::camelToSnake($field);
            if (!$model->dbProperties->offsetExists($property)) {
                throw new DBException("Invalid criteria \"{$field}\"!");
            }
        }

        return true;
    }
}
