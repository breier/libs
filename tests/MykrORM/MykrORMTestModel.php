<?php

/**
 * MykrORM Test Model File
 *
 * PHP version 7
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Test\MykrORM;

use Breier\MykrORM\MykrORM;
use PDO;

/**
 * MykrORM Test Model class
 */
class MykrORMTestModel extends MykrORM
{
    /**
     * DB Properties
     */
    protected $id;
    protected $date;
    protected $text;

    /**
     * Extra property
     */
    protected $extraProp;

    /**
     * Test-able DSN
     */
    public $testDSN;

    /**
     * Get DSN for PDO Connection
     */
    protected function getDSN(): string
    {
        return $this->testDSN ?? 'sqlite:testing.sqlite3';
    }

    /**
     * Destroy Database File
     */
    public function destroyDB(): bool
    {
        return unlink(__DIR__ . '/../../testing.sqlite3');
    }

    /**
     * Set test table name
     */
    public function __construct(string $tableName = null)
    {
        if (!empty($tableName)) {
            $this->dbTableName = $tableName;
        }

        $this->dbProperties = [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'text' => 'VARCHAR(256) NULL',
        ];

        parent::__construct();
    }

    /**
     * Expose Get Connection
     */
    public function exposedGetConnection(): PDO
    {
        return $this->getConnection();
    }

    /**
     * Expose Snake To Camel
     */
    public function snake2camel(string $value): string
    {
        return static::snakeToCamel($value);
    }

    /**
     * Expose Camel To Snake
     */
    public function camel2snake(string $value): string
    {
        return static::camelToSnake($value);
    }

    /**
     * Expose Create Table If Not Exists
     */
    public function exposedCreateTableIfNotExists(): void
    {
        $this->createTableIfNotExists();
    }

    /**
     * Set ID (no strict type int so test can fail)
     */
    public function setId($value)
    {
        return $this->id = $value;
    }

    /**
     * Set Date
     */
    public function setDate(string $value): string
    {
        return $this->date = $value;
    }

    /**
     * Set Text
     */
    public function setText(string $value): string
    {
        return $this->text = substr($value, 0, 256);
    }

    /**
     * Set Extra Property (non DB Property)
     *
     * @param mixed $value Anything
     *
     * @return mixed
     */
    public function setExtraProp($value)
    {
        $this->extraProp = $value;
    }

    /**
     * Insert Extra Property to DB Properties
     */
    public function insertExtraIntoDBProperties(string $type): void
    {
        $this->getDBProperties()->offsetSet('extra_prop', $type);
    }

    /**
     * Empty DB Properties
     */
    public function emptyDBProperties(): void
    {
        foreach ($this->getDBProperties()->keys() as $field) {
            $this->getDBProperties()->offsetUnset($field);
        }
    }
}
