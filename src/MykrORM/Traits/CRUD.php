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

        $fields = $this->dbProperties->filter(
            function ($type) {
                return stristr($type, 'SERIAL') === false
                    && stristr($type, 'INCREMENT') === false;
            }
        )->keys();

        $parameters = $this->dbProperties->keys()->map(
            function ($field) {
                $getter = 'get' . static::snakeToCamel($field);
                return call_user_func([static::class, $getter]);
            }
        );

        $fields = $fields->filter(
            function ($index) use ($parameters) {
                return $parameters->offsetGet($index) !== null;
            },
            ARRAY_FILTER_USE_KEY
        );

        $placeholders = ExtendedArray::fill(0, $fields->count(), '?');
        $parameters = $parameters->filter();

        $query = "INSERT INTO {$this->dbTableName}"
            . " ({$fields->implode(', ')}) VALUES"
            . " ({$placeholders->implode(', ')})";

        $this->getConnection()->beginTransaction();
        try {
            $preparedStatement = $this->getConnection()->prepare($query);
            $preparedStatement->execute($parameters->getArrayCopy());
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
     * @return static
     */
    public static function find($criteria)
    {
        static::validateCriteria($criteria);
        
        $model = new static();

        $placeholders = $criteria->keys()->map(
            function ($field) {
                $property = static::camelToSnake($field);
                return "{$property} = :{$field}";
            }
        )->implode(' AND ');

        try {
            $preparedStatement = $model->getConnection()->prepare(
                "SELECT * FROM {$model->dbTableName} WHERE {$placeholders}"
            );
            $preparedStatement->execute($criteria->getArrayCopy());

            return $preparedStatement->fetchObject(static::class);
        } catch (PDOException $e) {
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
