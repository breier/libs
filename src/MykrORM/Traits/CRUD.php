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
                return stristr($type, 'SERIAL') === false;
            },
            ARRAY_FILTER_USE_KEY
        )->keys();

        $placeholders = ExtendedArray::fill(0, $fields->count(), '?');

        $query = "INSERT INTO {$this->dbTableName}"
            . " ({$fields->implode(', ')}) VALUES"
            . " ({$placeholders->implode(', ')})";

        $parameters = $this->dbProperties->keys()->map(
            function ($field) {
                return $this->{$field} ?? null;
            }
        );

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
                return "{$field} = :{$field}";
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
            if (!$model->dbProperties->offsetExists($field)) {
                throw new DBException("Invalid criteria \"{$field}\"!");
            }
        }

        return true;
    }
}
