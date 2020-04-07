<?php

/**
 * MykrORM Test False Model File
 *
 * PHP version 7
 *
 * @category Tests
 * @package  Breier/Libs
 * @author   Andre Breier <andre@breier.net.br>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Test\MykrORM;

use Breier\ExtendedArray\ExtendedArray;
use Breier\MykrORM\MykrORM;
use PDO;

/**
 * MykrORM Test False Model class
 */
class MykrORMTestFalseModel extends MykrORM
{
    /**
     * DB Properties
     */
    protected $name;
    protected $isValid;

    /**
     * Get DSN for PDO Connection
     */
    protected function getDSN(): string
    {
        return 'sqlite:testing.sqlite3';
    }

    /**
     * Set test table name
     */
    public function __construct()
    {
        $this->dbProperties = [
            'name' => 'TEXT',
            'is_valid' => 'BOOL',
        ];

        parent::__construct();
    }

    /**
     * Expose Get Properties
     */
    public function exposedGetProperties(): ExtendedArray
    {
        return $this->getProperties();
    }

    /**
     * Set Name (no strict type int so test can fail)
     */
    public function setName($value)
    {
        return $this->name = $value;
    }

    /**
     * Set Is Valid
     */
    public function setIsValid(bool $value): bool
    {
        return $this->isValid = $value;
    }
}
