# MykrORM Docs
This library started with the idea of providing less than minimal DB
functionality with still some intuitive automated stuff.

In order to handle data models and its properties in a clean code manner
(readable and maintainable) I developed this library on top of PDO.
Please enjoy (use at your own risk XD).

_\* You can find all the methods from PDO and their documentation at
[php.net/manual/class.pdo](https://www.php.net/manual/en/class.pdo.php)._

Table of Contents:
* [Model Example](#model-example)
* [Methods in MykrORM](#methods-in-mykrorm)
* [CRUD Methods](#crud-methods)
* [Table Management](#table-management)

## Model Example
This is a code example of a model that extends MykrORM
```php
<?php

namespace App\Model;

use DateTime;
use Breier\MykrORM\MykrORM;
use Breier\MykrORM\Exception\DBException;

class Session extends MykrORM
{
  protected $token;
  protected $email;
  protected $startTime;

  protected function getDSN(): string
  {
    return 'pgsql:host=localhost;port=5432;dbname=test;user=test;password=1234';
  }

  public function __construct()
  {
    parent::__construct();

    $this->dbProperties = [
      'token' => 'CHAR(64) NOT NULL PRIMARY KEY',
      'email' => 'VARCHAR(64) NOT NULL',
      'start_time' => 'TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ];
  }

  public function setToken(string $value = ''): string
  {
    if (strlen($value) === 64) {
      return $this->token = $value;
    }
    $secret = 'my-app-hash-secret-123';
    $this->token = hash('sha256', "{$secret}-{$this->email}-" . microtime(true));
    return $this->token;
  }

  public function setEmail(string $value): string
  {
    $value = filter_var($value, FILTER_VALIDATE_EMAIL);
    return $this->email = $value;
  }

  public function setStartTime($value): DateTime
  {
    if ($value instanceof DateTime) {
      return $this->startTime = $value;
    }
    return $this->startTime = new DateTime($value);
  }
}
```

### Further Information
The first time you try to "Create" an entry of that model, the table will be
created in the database.

If you update the model adding more columns they will be added on creation as well.

_\*If you have a boolean DB property you have to set it as DEFAULT FALSE to work properly._

While Create, Update and Delete deal with the current instance,
"Read" (`find`) returns an ExtendedArray of instances of the Model.

Properties listed in $this->dbProperties should be declared with protected visibility.
MykrORM will provide automatic getters for them.

## Methods in MykrORM
This is the abstract class that implements the ORM
and it's also the base for every model you wish to create.
* The model class that extends from it has to implement `getDSN()`
  that returns a DSN string to PDO connection;
* It sets these default PDO options:
  * `PDO::ATTR_ERRMODE` -> `PDO::ERRMODE_EXCEPTION`
  * `PDO::ATTR_DEFAULT_FETCH_MODE` -> `PDO::FETCH_NAMED`
* It sets the DB table name based on the model class name that extended from it.
<br>But you can override it by setting `$this->dbTableName` before `parent::__construct()`;
* If you use parameters for the extended `__construct` you also need to set
<br>`$this->dbConstructorArgs` as an array containing the parameters' values in it;
* It provides automatic getters via `__call` for related DB properties;
* It maps setters via `__set` for `fetchObject()` PDO mode;

### `abstract protected function getDSN(): string`
Returns the DSN for PDO connection (has to be implemented by the extending class).
<details>
  <summary>Code Example</summary>

  ```php
  class Test extends MykrORM
  {
    protected function getDSN(): string
    {
      return 'pgsql:host=localhost;port=5432;dbname=test;user=test;password=1234';
      // return 'sqlite:messaging.sqlite3'; // local option ;)
    }
  }
  ```
</details>

### `protected function getDBProperties(): ExtendedArray`
Get DB Properties (ensure it is an ExtendedArray instance)
<details>
  <summary>Code Example</summary>

  ```php
  class Test extends MykrORM
  {
    public function test(): void
    {
      $this->getDBProperties()->keys()->join('/'); // 'token/email/start_time'
    }
  }
  ```
</details>

### `protected function getConnection(): PDO`
Gets the stored PDO object with a valid connection.
<details>
  <summary>Code Example</summary>

  ```php
  class Test extends MykrORM
  {
    public function test(): void
    {
      $this->getConnection()->query('SELECT * FROM test');
    }
  }
  ```
</details>

### `public function __call(string $name, array $arguments)`
Provides automatic getters for DB properties.
<details>
  <summary>Code Example</summary>

  ```php
  class Test extends MykrORM
  {
    protected $test = 1234;
    protected $other = "no-getter";
    public __construct()
    {
      $this->dbProperties = [
        'test' => 'INT NOT NULL PRIMARY KEY',
      ];
    }
  }
  (new Test())->getTest(); // 1234
  (new Test())->getOther(); // throws DBException property is not DB property!
  ```
</details>

### `public function __set(string $name, $value): void`
Maps setters automatically for `fetchObject()` PDO mode.
<details>
  <summary>Code Example</summary>

  ```php
  class Test extends MykrORM
  {
    protected $testName = 'test';
    public __construct()
    {
      $this->dbProperties = [
        'test_name' => 'CHAR(4) NOT NULL PRIMARY KEY',
      ];
    }
    public setTestName(string $value): string
    {
      $this->testName = $value;
    }
    public test(): void
    {
      $preparedStatement = $this->getConnection()->prepare("SELECT * FROM {$this->dbTableName}");
      $preparedStatement->execute();

      $likeThis = $preparedStatement->fetchObject(static::class);

      if (!empty($likeThis)) {
        $likeThis->getTestName(); // works because __set mapped 'test_name' to 'setTestName'
      }
    }
  }
  ```
</details>

### `protected static function camelToSnake(string $string): string`
`[static]` Converts Camel-Case to Snake-Case (from Property to DB).
<details>
  <summary>Code Example</summary>

  ```php
  class Test extends MykrORM
  {
    public function test(): void
    {
      static::camelToSnake('anotherTestName'); // another_test_name
    }
  }
  ```
</details>

### `protected static function snakeToCamel(string $string): string`
`[static]` Converts Snake-Case to Camel-Case (from DB to Property).
<details>
  <summary>Code Example</summary>

  ```php
  class Test extends MykrORM
  {
    public function test(): void
    {
      static::snakeToCamel('test_name'); // TestName
    }
  }
  ```
</details>

## CRUD Methods
This methods are embedded in MykrORM but I rather list them here for better organization.

### `public function create(): void`
Insert new row to the model table with current properties.
<details>
  <summary>Code Example</summary>

  ```php
  $test = new Test();
  $test->setTestName('what');
  $test->create();
  ```
</details>

### `public function find($criteria): ExtendedArray`
Get all rows of the model table that matches $criteria
(returns an ExtendedArray with model instances).

_\* There's a criteria validator in place here that can be further implemented by the model._
<details>
  <summary>Code Example</summary>

  ```php
  $testModel = new Test();
  $test = $testModel->find(['test_name' => 'what']); // ExtendedArray
  $test->first()->element(); // Test Model instance (or null)
  $test->next()->element(); // Test Model instance of second row (or null)
  ```
</details>

### `public function update($criteria): void`
Update a row of the model table that matches $criteria.

_\* It internally uses `find` to get the original object._
<details>
  <summary>Code Example</summary>

  ```php
  $test = (new Test())->find(['test_name' => 'what']);
  if ($test->count()) {
    $testModel = $test->first()->element();
    $testModel->setTestName('soap');
    $testModel->update(['test_name' => 'what']);
  }
  ```
</details>

### `public function delete(): void`
Delete a row of the model table with current properties.
<details>
  <summary>Code Example</summary>

  ```php
  $test = (new Test())->find(['test_name' => 'soap']);
  if ($test->count()) {
    $testModel = $test->first()->element();
    $testModel->delete();
  }
  ```
</details>

### `public function validateCriteria($criteria): bool`
Make sure that any key in the criteria matches "dbProperties".
<details>
  <summary>Code Example</summary>

  ```php
  $testModel = new Test();
  $testModel->validateCriteria([]); // true
  $testModel->validateCriteria(['test_name' => null]); // true
  $testModel->validateCriteria(['test_name' => 'soap']); // true
  $testModel->validateCriteria(['test_non_existent' => 'soap']); // Throws DBException
  ```
</details>

### `protected function getProperties(): ExtendedArray`
Get database available properties in an associative array manner.
<details>
  <summary>Code Example</summary>

  ```php
  $test = new Test();
  $test->setTestName('soap');
  print($test->getProperties()); // {"test_name":"soap"}
  ```
</details>

### `protected function bindIndexedParams(PDOStatement $statement, ExtendedArray $parameters): void`
Binds parameters to indexed (?) placeholders in the prepared statement.
<details>
  <summary>Code Example</summary>

  ```php
  class Test extends MykrORM
  {
    protected $testName = 'test';
    public __construct()
    {
      $this->dbProperties = [
        'test_name' => 'CHAR(4) NOT NULL PRIMARY KEY',
      ];
    }
    public setTestName(string $value): string
    {
      $this->testName = $value;
    }
    public testUpdate(): void
    {
      $query = "UPDATE {$this->dbTableName} SET test_name = ? WHERE test_name = ?";
      $parameters = new ExtendedArray(['test_name' => 'newValue', 0 => 'oldValue']);
      $preparedStatement = $this->getConnection()->prepare($query);
      $this->bindIndexedParams($preparedStatement, $parameters);
      $preparedStatement->execute();
    }
  }
  ```
</details>

### `protected function findPrimaryKey(): string`
Get DB property set as 'PRIMARY KEY' (or the first index if not found).
<details>
  <summary>Code Example</summary>

  ```php
  class Test extends MykrORM
  {
    public __construct()
    {
      $this->dbProperties = [
        'test_name' => 'CHAR(4) NOT NULL PRIMARY KEY',
      ];
    }
    public function test(): void
    {
      $this->findPrimaryKey(); // 'test_name'
    }
  }
  ```
</details>

## Table Management
A little bit of magic that actually limits the DB structure to a very simple one.

### `protected function createTableIfNotExists(): void`
Checks if a table exists for the current extending model.
<br>If it doesn't, it creates it.
<br>If it does, it further checks for alterations to alter it.
<details>
  <summary>Code Example</summary>

  ```php
  class Test extends MykrORM
  {
    ...
    public function test(): void
    {
      $this->dbTableName = 'different_test';
      $this->createTableIfNotExists(); // creates new table with same DB properties
      ...
    }
  }
  ```
</details>
