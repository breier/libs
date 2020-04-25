<?php

/**
 * PHP Version 7
 *
 * Micro ORM File
 *
 * @category Extended_Class
 * @package  Breier\Libs
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Breier\MykrORM;

use PDO;
use PDOException;
use Breier\ExtendedArray\ExtendedArray;
use Breier\MykrORM\Exception\DBException;
use Breier\MykrORM\Traits\{TableManager, CRUD};

/**
 * Micro ORM Model class
 */
abstract class MykrORM
{
    use TableManager;
    use CRUD;

    /**
     * @var PDO Connection
     */
    private $dbConn;

    /**
     * @var array PDO Options
     */
    private $dbOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NAMED,
    ];

    /** @var string DB Table Name */
    protected $dbTableName;

    /** @var ExtendedArray DB Properties (table columns) */
    protected $dbProperties;

    /** @var array DB Constructor Args */
    protected $dbConstructorArgs = [];

    /**
     * Set defaults
     */
    public function __construct()
    {
        if (empty($this->dbTableName)) {
            $this->dbTableName = basename(
                str_replace('\\', '/', strtolower(static::class))
            );
        }
    }

    /**
     * Get DSN for PDO Connection [has to be implemented]
     */
    abstract protected function getDSN(): string;

    /**
     * Get DB Properties (ensure it is an ExtendedArray instance)
     */
    protected function getDBProperties(): ExtendedArray
    {
        if ($this->dbProperties instanceof ExtendedArray) {
            return $this->dbProperties;
        }

        $this->dbProperties = new ExtendedArray($this->dbProperties);
        return $this->dbProperties;
    }

    /**
     * Get PDO Connection
     *
     * @throws DBException
     */
    protected function getConnection(): PDO
    {
        if ($this->dbConn instanceof PDO) {
            return $this->dbConn;
        }

        try {
            $this->dbConn = new PDO($this->getDSN(), null, null, $this->dbOptions);
        } catch (PDOException $e) {
            throw new DBException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->dbConn;
    }

    /**
     * Automatic Getters for DB fields
     *
     * @return mixed
     * @throws DBException
     */
    public function __get(string $propertyName)
    {
        if (!property_exists($this, $propertyName)) {
            throw new DBException('Property does not exist!');
        }

        $dbFields = $this->getDBProperties()->keys();
        if (!$dbFields->contains(self::camelToSnake($propertyName))) {
            throw new DBException('Property is not DB property!');
        }

        return $this->{$propertyName};
    }

    /**
     * Setter Mapper for DB fields
     *
     * @throws DBException
     */
    public function __set(string $name, $value): void
    {
        if (!property_exists($this, lcfirst(self::snakeToCamel($name)))) {
            throw new DBException('Property does not exist!');
        }

        $dbFields = $this->getDBProperties()->keys();
        if ($dbFields->count() && !$dbFields->contains(self::camelToSnake($name))) {
            throw new DBException('Property is not DB property!');
        }

        $setter = 'set' . self::snakeToCamel($name);
        $this->{$setter}($value);
    }

    /**
     * Snake To Camel case
     */
    final protected static function snakeToCamel(string $string): string
    {
        return str_replace('_', '', ucwords($string, '_'));
    }

    /**
     * Camel To Snake case
     */
    final protected static function camelToSnake(string $string): string
    {
        return strtolower(
            preg_replace(
                '/([a-z])([A-Z0-9])/',
                '$1_$2',
                preg_replace(
                    '/([A-Z0-9]+)([A-Z][a-z])/',
                    '$1_$2',
                    $string
                )
            )
        );
    }
}
