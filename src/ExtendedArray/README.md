# Extended Array Docs
This library started from a discussion on whether it's intuitive or not
to get the first element of an array using "reset()". Well I think it's not!

In order to handle arrays and its elements in a clean code manner
(readable and maintainable) I developed this library. Please enjoy
(use at your own risk XD).

Table of Contents:
* [Methods from ArrayIterator](#methods-from-arrayiterator)
* [Methods in ExtendedArrayBase](#methods-in-extendedarraybase)
* [Methods in ExtendedArray](#methods-in-extendedarray)

## Methods from ArrayIterator
You can find all the methods and their documentation at
[php.net/manual/class.arrayiterator](https://www.php.net/manual/en/class.arrayiterator.php).

<details>
  <summary>But here is a list of non-modified methods:</summary>

  | Method       | Parameters         | Return | Description |
  | ------------ | ------------------ | ------ | ----------- |
  | append       | mixed $value       | null   | `[*]` Append an element to the object
  | count        |                    | int    | The amount of elements
  | current      |                    | mixed  | Get the element under the cursor
  | getFlags     |                    | int    | Get behaviour flags of the ArrayIterator
  | key          |                    | mixed  | Current position element index
  | offsetGet    | mixed $index       | mixed  | Get element in given index
  | seek         | int $position      | null   | Moves the cursor to given position
  | serialize    |                    | string | Applies PHP serialization to the object
  | setFlags     | string $flags      | null   | Set behaviour flags of the ArrayIterator
  | unserialize  | string $serialized | null   | Populates self using PHP unserialize
  | valid        |                    | bool   | Validate element in the current position

  _* "append" is indirectly modified as it uses "offsetSet" internally_
</details>

## Methods in ExtendedArrayBase
This is the abstract class that modifies the behaviour of ArrayIterator
to improve its use as a clean Object Oriented Class.
* It accepts any of the following types as a parameter to be instantiated:
  * (null, array, \SplFixedArray, \ArrayObject, \ArrayIterator);
* It sets the default flag to ARRAY_AS_PROPS;
* It converts all sub-arrays into sub-instances of its class;
* It uses an internal positioning system to help navigate through (next, prev, ...);
* It has a magic __toString method that returns JSON;

<details>
  <summary>Here's the complete list of methods:</summary>

  | Method        | Parameters             | Return | Description |
  | ------------- | ---------------------- | ------ | ----------- |
  | asort         |                        | this   | Extending method to support sub-objects
  | element       |                        | mixed  | `[added]` Element is an alias for "current"
  | end           |                        | this   | `[added]` Move the cursor to the end
  | first         |                        | this   | `[added]` First is an alias for "rewind"
  | getArrayCopy  |                        | array  | Extending method to convert sub-objects to array
  | isArrayObject | mixed $array           | bool   | `[added][static]` Identifies usable classes
  | jsonSerialize | <nobr>[int $options[, int $depth]]</nobr> | string | `[added]` JSON Serialize
  | ksort         |                        | this   | Extending method to update position map
  | last          |                        | this   | `[added]` Last is an alias to "end"
  | natcasesort   |                        | this   | Extending method to update position map
  | natsort       |                        | this   | Extending method to update position map
  | next          |                        | this   | Extending method to return $this
  | offsetExists  | mixed $index           | bool   | Extending method to behave like "array_key_exists"
  | offsetSet     | <nobr>mixed $index, mixed $newval</nobr>  | null   | Extending method to update position map
  | offsetUnset   | mixed $index           | null   | Extending method to update position map
  | prev          |                        | this   | `[added]` Move the cursor to previous element
  | rewind        |                        | this   | Extending method to return $this
  | uasort        | callable $cmp_function | this   | Extending method to update position map
  | uksort        | callable $cmp_function | this   | Extending method to update position map
</details>

## Methods in ExtendedArray

### `arsort(): ExtendedArray`
Reverse sort by element.
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(['Dublin', 'Paris', 'Cairo', 'Tokyo']);
  print($cities->arsort());
  /**
   * {
   *   "3": "Tokyo",
   *   "1": "Paris",
   *   "0": "Dublin",
   *   "2": "Cairo"
   * }
   */
  ```
</details>

### `contains(mixed $needle[, bool $strict]): bool`
Contains is similar to "in_array" with object support.
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(['Dublin', 'Paris', 'Cairo', 'Tokyo']);
  var_dump($cities->contains('Cairo')); // true
  var_dump($cities->contains('Kyoto')); // false
  var_dump($cities->contains($cities->{1})); // true
  ```
</details>

### `filter([callable $callback[, int $flag]]): ExtendedArray`
A poly-fill for "array_filter".
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(['Dublin', 'Paris', 'Cairo', 'Tokyo']);
  print(
    $cities->filter(
      function ($item) {
        return strlen($item) == 5;
      }
    )
  );
  /**
   * {
   *   "1": "Paris",
   *   "2": "Cairo",
   *   "3": "Tokyo"
   * }
   */
  ```
</details>

### `filterWithObjects([callable $callback[, int $flag]]): ExtendedArray`
Extending filter to support objects.
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(['Dublin', 'Paris', 'Cairo', 'Tokyo']);
  $countries = new ExtendedArray(['Ireland', 'France', 'Egypt', 'Japan']);
  $places = new ExtendedArray(['cities' => $cities, 'countries' => $countries]);
  print(
    $places->filterWithObjects(
      function ($item) {
        return $item->count();
      }
    )
  );
  /**
   * {
   *   "cities": {
   *     "0": "Dublin",
   *     "1": "Paris",
   *     "2": "Cairo",
   *     "3": "Tokyo"
   *   },
   *   "countries": {
   *     "0": "Ireland",
   *     "1": "France",
   *     "2": "Egypt",
   *     "3": "Japan"
   *   }
   * }
   */
  ```
</details>

### `fromJSON(string $json[, int $depth]): ExtendedArray`
`[static]` Instantiate from a JSON string.
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $jsonCities = '{"Dublin","Paris","Cairo","Tokyo"}';
  print(
    ExtendedArray::fromJSON($jsonCities)
  );
  /**
   * {
   *   "0": "Dublin",
   *   "1": "Paris",
   *   "2": "Cairo",
   *   "3": "Tokyo"
   * }
   */
  ```
</details>

### `isArray(mixed $element): bool`
`[static]` Validates any type of array.
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $plainArray = ['Dublin', 'Paris', 'Cairo', 'Tokyo'];
  $cities = new ExtendedArray($plainArray);
  var_dump(ExtendedArray::isArray($plainArray)); // true
  var_dump(ExtendedArray::isArray($cities));     // true
  var_dump(ExtendedArray::isArray('not array')); // false
  ```
</details>

### `join(string $glue = ''): string`
Concatenate array values in a string separated by $glue
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(['Dublin', 'Paris', 'Cairo', 'Tokyo']);
  print($cities->join(',')); // Dublin,Paris,Cairo,Tokyo
  ```
</details>

### `keys(): ExtendedArray`
Get this array keys (properties' names).
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(
    [
      'Ireland' => 'Dublin',
      'France' => 'Paris',
      'Egypt' => 'Cairo',
      'Japan' => 'Tokyo'
    ]
  );
  print($cities->keys());
  /**
   * {
   *   "0": "Ireland",
   *   "1": "France",
   *   "2": "Egypt",
   *   "3": "Japan"
   * }
   */
  ```
</details>

### `krsort(): ExtendedArray`
Reverse sort by keys.
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(
    [
      'Ireland' => 'Dublin',
      'France' => 'Paris',
      'Egypt' => 'Cairo',
      'Japan' => 'Tokyo'
    ]
  );
  print($cities->krsort());
  /**
   * {
   *   "Japan": "Tokyo",
   *   "Ireland": "Dublin",
   *   "France": "Paris",
   *   "Egypt": "Cairo"
   * }
   */
  ```
</details>

### `map(callable $callback[, array ...$params]): ExtendedArray`
A poly-fill for "array_map".
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(['Dublin', 'Paris', 'Cairo', 'Tokyo']);
  print(
    $cities->map(
      function ($item) {
        return strrev($item);
      }
    )
  );
  /**
   * {
   *   "0": "nilbuD",
   *   "1": "siraP",
   *   "2": "oriaC",
   *   "3": "oykoT"
   * }
   */
  ```
</details>

### `mapWithObjects(callable $callback[, array ...$params]): ExtendedArray`
Extending map to support objects.
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(['Dublin', 'Paris', 'Cairo', 'Tokyo']);
  $countries = new ExtendedArray(['Ireland', 'France', 'Egypt', 'Japan']);
  $places = new ExtendedArray(['cities' => $cities, 'countries' => $countries]);
  print(
    $places->mapWithObjects(
      function ($item) {
        return $item->filter(
          function ($element) {
            return strlen($element) == 5;
          }
        )->jsonSerialize();
      }
    )
  );
  /**
   * {
   *   "cities": "{\"1\":\"Paris\",\"2\":\"Cairo\",\"3\":\"Tokyo\"}",
   *   "countries": "{\"2\":\"Egypt\",\"3\":\"Japan\"}"
   * }
   */
  ```
</details>

### `offsetGetFirst(): mixed`
Gets first element without moving the array cursor.
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(['Dublin', 'Paris', 'Cairo', 'Tokyo']);
  print($cities->offsetGetFirst());
  /**
   * Dublin
   */
  ```
</details>

### `offsetGetLast(): mixed`
Gets last element without moving the array cursor.
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(['Dublin', 'Paris', 'Cairo', 'Tokyo']);
  print($cities->offsetGetLast());
  /**
   * Tokyo
   */
  ```
</details>

### `offsetGetPosition(int $position): mixed`
Gets element in the given position without moving the array cursor.
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(['Dublin', 'Paris', 'Cairo', 'Tokyo']);
  print($cities->offsetGetPosition(1));
  /**
   * Paris
   */
  ```
</details>

### `shuffle(): ExtendedArray`
Shuffle elements randomly
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(['Dublin', 'Paris', 'Cairo', 'Tokyo']);
  print($cities->shuffle());
  /**
   * {
   *   "1": "Paris",
   *   "0": "Dublin",
   *   "3": "Tokyo",
   *   "2": "Cairo"
   * }
   */
  ```
</details>

### `values(): ExtendedArray`
Get values in a numbered key array
<details>
  <summary>Code Example</summary>

  ```php
  <?php
  $cities = new ExtendedArray(
    [
      'Egypt' => 'Cairo',
      'Japan' => 'Tokyo',
      'France' => 'Paris',
      'Ireland' => 'Dublin'
    ]
  );
  print($cities->values());
  /**
   * {
   *   "0": "Cairo",
   *   "1": "Tokyo",
   *   "2": "Paris",
   *   "3": "Dublin"
   * }
   */
  ```
</details>
