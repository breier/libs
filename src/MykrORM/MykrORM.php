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
use Breier\MykrORM\Exception\{DBException, UndefinedMethodException};
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
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * @var string Table Name
     */
    protected $dbTableName;

    /**
     * @var ExtendedArray DB Properties (table columns)
     */
    protected $dbProperties;

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
     * Automatic Getters [nothing is really private]
     *
     * @return mixed
     * @throws DBException
     * @throws UndefinedMethodException
     */
    public function __call(string $name, array $arguments)
    {
        if (preg_match('/^get[A-Z][\w\d]*/', $name) !== 1) {
            throw new UndefinedMethodException(
                'Attempted to call an undefined method named'
                . " \"{$name}\" of class \"{" . static::class . "}\"."
            );
        }

        $propertyName = strtolower($name[3]) . substr($name, 4);
        if (!property_exists($this, $propertyName)) {
            throw new DBException('Property does not exist!');
        }

        return $this->{$propertyName};
    }

    /**
     * Setter Mapper for PDO
     */
    public function __set(string $name, $value): void
    {
        $setter = 'set' . static::snakeToCamel($name);
        if (method_exists($this, $setter)) {
            $this->{$setter}($value);
        }
    }

    /**
     * Snake To Camel case
     */
    protected static function snakeToCamel(string $string): string
    {
        return str_replace('_', '', ucwords($string, '_'));
    }

    /**
     * Camel To Snake case
     */
    protected static function camelToSnake(string $string): string
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
