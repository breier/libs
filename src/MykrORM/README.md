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
use Breier\ExtendedArray\ExtendedArray;
use Breier\MykrORM\Exception\DBException;
use Breier\MykrORM\MykrORM;

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

    $this->dbProperties = new ExtendedArray([
      'token' => 'CHAR(64) NOT NULL PRIMARY KEY',
      'email' => 'VARCHAR(64) NOT NULL',
      'start_time' => 'TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ]);
  }

  public static function validateCriteria($criteria): bool
  {
    parent::validateCriteria($criteria);
    if ($criteria->count() > 1) {
      throw new DBException("Invalid criteria!");
    }
    if (!$criteria->offsetExists('token')) {
      throw new DBException("Invalid criteria!");
    }
    return true;
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

### Further Examples
The first time you try to "Create" an entry of that model, the table will be
created in the database.

If you update the model adding more columns they will be added on creation as well.

While Create, Update and Delete are instance methods, "Read" (`find`) is static
and returns an ExtendedArray of instances of the Model.

It's recommended to have your properties visibility set to private, but MykrORM
will create automatic getters for the ones listed in $this->dbProperties.
```php
$session = Session::find(['token' => $AuthrizationBearerToken]);
```

## Methods in MykrORM
This is the abstract class that implements the ORM
and it's also the base for every model you wish to create.
* The model class that extends from it has to implement `getDSN()`
  that returns a DSN string to PDO connection;
* It sets the default PDO options:
  * `PDO::ATTR_ERRMODE` -> `PDO::ERRMODE_EXCEPTION`
  * `PDO::ATTR_DEFAULT_FETCH_MODE` -> `PDO::FETCH_NAMED`
  * `PDO::ATTR_EMULATE_PREPARES` -> `false`
* It sets the DB table name based on the model class name that extended from it.
<br>But you can override it by setting `$this->dbTableName` before `parent::__construct()`;
* It provides automatic getters via `__call` for related DB properties;
* It maps setters via `__set` for `fetchObject()` PDO mode;

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
    private $test = 1234;
    private $other = "private";
    public __construct()
    {
      $this->dbProperties = new ExtendedArray([
        'test' => 'INT NOT NULL PRIMARY KEY',
      ]);
    }
  }
  (new Test())->getTest(); // 1234
  (new Test())->getOther(); // throws DBException property does not exist!
  ```
</details>

### `public function __set(string $name, $value): void`
Maps setters automatically for `fetchObject()` PDO mode.
<details>
  <summary>Code Example</summary>

  ```php
  class Test extends MykrORM
  {
    private $testName = 'test';
    public __construct()
    {
      $this->dbProperties = new ExtendedArray([
        'test_name' => 'CHAR(4) NOT NULL PRIMARY KEY',
      ]);
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
        $likeThis->getTestName(); // first row value found in DB
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
This methods are embedded in MykrORM but I rather list them here for better oganization.

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

### `public static function find($criteria): ExtendedArray`
`[static]` Get all rows of the model table that matches $criteria
(returns an ExtendedArray with model instances).

_\* There's a criteria validator in place here that can be further implemented by the model._
<details>
  <summary>Code Example</summary>

  ```php
  $test = Test::find(['test_name' => 'what']); // ExtendedArray
  $test->first()->element(); // Test Model instance (or null)

  $otherTest = Test::find(['test_name' => ['what', 'is', 'up']]); // ExtendedArray
  $otherTest->next()->element(); // Test Model instance of second row (or null)
  ```
</details>

### `public function update($criteria): void`
Update a row of the model table that matches $criteria.

_\* It internally uses `find` to get the original object._
<details>
  <summary>Code Example</summary>

  ```php
  $test = Test::find(['test_name' => 'what']);
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
  $test = Test::find(['test_name' => 'soap']);
  if ($test->count()) {
    $testModel = $test->first()->element();
    $testModel->delete();
  }
  ```
</details>

### `public function getProperties(): ExtendedArray`
Get database available properties in an associative array manner.
<details>
  <summary>Code Example</summary>

  ```php
  $test = Test::find(['test_name' => 'soap']);
  if ($test->count()) {
    print($test->current()->getProperties()); // {"test_name":"soap"}
  }
  ```
</details>

### `public static function validateCriteria($criteria): bool`
Make sure that any key in criteria matches "dbProperties" and that they're not empty.
<details>
  <summary>Code Example</summary>

  ```php
  if (Test::validateCriteria(['test_name' => 'soap'])) {
    $test = Test::find(['test_name' => 'soap']);
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
      $this->createTableIfNotExists();
      ...
    }
  }
  ```
</details>
