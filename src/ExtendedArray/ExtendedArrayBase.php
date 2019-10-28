<?php
/**
 * PHP Version 7
 *
 * Extended Array Base Abstract Class to improve array handling
 *
 * @category Extended_Class
 * @package  Breier\Libs
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     none.io
 */

namespace Breier\ExtendedArray;

use \SplFixedArray;
use \ArrayIterator;
use \ArrayObject;

/**
 * ArrayIterator Class Entities
 *
 * @property int STD_PROP_LIST  = 1;
 * Properties of the object have their normal functionality when accessed as list
 * @property int ARRAY_AS_PROPS = 2;
 * Entries can be accessed as properties (read and write)
 *
 * @method null append(mixed $value); Append an element to the object
 * @method int count(); The amount of elements
 * @method mixed current(); Get the element under the cursor
 * @method int getFlags(); Get behaviour flags of the ArrayIterator
 * @method mixed key(); Current position element index
 * @method null natcasesort(); Sort elements using case insensitive "natural order"
 * @method null natsort(); Sort elements using "natural order"
 * @method bool offsetExists(mixed $index); Validate element index
 * @method mixed offsetGet(mixed $index); Get element in given index
 * @method string serialize(); Applies PHP serialization to the object
 * @method null setFlags(string $flags); Set behaviour flags of the ArrayIterator
 * @method null uasort(callable $cmp_function); Sort by elements using given function
 * @method null uksort(callable $cmp_function); Sort by indexes using given function
 * @method null unserialize(string $serialized); Populates self using PHP unserialize
 * @method bool valid(); Validate element in the current position
 *
 * Extended Array Base Abstract Class to improve array handling
 *
 * @category Extended_Class
 * @package  Breier\Libs
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     none.io
 */
abstract class ExtendedArrayBase extends ArrayIterator
{
    private $_positionMap;
    private $_lastCursorKey;

    /**
     * Instantiate an Extended Array
     *
     * @param array $array To be parsed into properties
     * @param int   $flags (STD_PROP_LIST | ARRAY_AS_PROPS)
     */
    public function __construct($array = null, int $flags = 2)
    {
        if ($array instanceof ArrayIterator || $array instanceof ArrayObject) {
            $array = $array->getArrayCopy();
        }

        if ($array instanceof SplFixedArray) {
            $array = $array->toArray();
        }

        if (empty($array)) {
            $array = [];
        }

        foreach ($array as &$item) {
            if (is_array($item)) {
                $item = new static($item);
            }
        }

        parent::__construct($array, $flags);

        $this->updatePositionMap();
    }

    /**
     * Extending ASort Method to update position map
     * Sort ascending by elements
     *
     * @return null
     */
    public function asort(): void
    {
        $this->uasort(
            function ($a, $b) {
                if (static::isArrayObject($a)) {
                    $a = $a->getArrayCopy();
                }
                if (static::isArrayObject($b)) {
                    $b = $b->getArrayCopy();
                }
                return $a <=> $b;
            }
        );

        $this->updatePositionMap();
    }

    /**
     * Element is an alias for Current
     *
     * @return mixed
     */
    public function element()
    {
        return $this->current();
    }

    /**
     * Move the Cursor to the End, poly-fill for `end`
     *
     * @return ExtendedArray
     */
    public function end(): ExtendedArray
    {
        if ($this->count()) {
            $this->seek($this->count() -1);
        }

        return $this;
    }

    /**
     * First is an alias for Rewind
     *
     * @return ExtendedArray
     */
    public function first(): ExtendedArray
    {
        return $this->rewind();
    }

    /**
     * Extending Get Array Copy to convert sub-items to array
     *
     * @return array
     */
    public function getArrayCopy(): array
    {
        $plainArray = parent::getArrayCopy();

        foreach ($plainArray as &$item) {
            if ($item instanceof ExtendedArrayBase) {
                $item = $item->getArrayCopy();
            }
        }

        return $plainArray;
    }

    /**
     * Is Array Object identifies usable classes
     *
     * @param mixed $array The object to be validated
     *
     * @return bool
     */
    public static function isArrayObject($array): bool
    {
        return (
            $array instanceof ExtendedArrayBase
            || $array instanceof ArrayIterator
            || $array instanceof ArrayObject
        );
    }

    /**
     * Extending KSort Method to update position map
     * Sort ascending by element indexes
     *
     * @return null
     */
    public function ksort(): void
    {
        parent::ksort();

        $this->updatePositionMap();
    }

    /**
     * Last is an alias to End
     *
     * @return ExtendedArray
     */
    public function last(): ExtendedArray
    {
        return $this->end();
    }

    /**
     * Extending next Method to return ExtendedArray instead of void
     *
     * @return ExtendedArray
     */
    public function next(): ExtendedArray
    {
        parent::next();

        return $this;
    }

    /**
     * Extending Offset Set Method to update position map
     * Set an element with index name
     *
     * @param int|string $index  Key of the item
     * @param mixed      $newval to be set
     *
     * @return null
     */
    public function offsetSet($index, $newval): void
    {
        $isAppend = !$this->offsetExists($index);

        parent::offsetSet($index, $newval);

        if ($isAppend) {
            $this->_appendPositionMap($index);
        }
    }

    /**
     * Extending Offset Unset Method to update position map
     * Remove an element
     *
     * @param int|string $index Key of the item
     *
     * @return null
     */
    public function offsetUnset($index): void
    {
        parent::offsetUnset($index);

        $this->updatePositionMap();
    }

    /**
     * Current Position, poly-fill for `pos` of SplFixedArray
     *
     * @return int
     */
    public function pos(): int
    {
        return array_search(
            $this->key(),
            $this->_positionMap,
            true
        );
    }

    /**
     * Move the Cursor to Previous element
     *
     * @return ExtendedArray
     */
    public function prev(): ExtendedArray
    {
        $currentPosition = $this->pos();

        if (!$currentPosition) {
            return $this->end()->next();
        }

        return $this->seek($currentPosition - 1);
    }

    /**
     * Extending Rewind Method to return ExtendedArray instead of void
     * Move the cursor to initial position
     *
     * @return ExtendedArray
     */
    public function rewind(): ExtendedArray
    {
        parent::rewind();

        return $this;
    }

    /**
     * Extending Seek Method to return ExtendedArray instead of void
     *
     * @param int $position To seek
     *
     * @return ExtendedArray
     */
    public function seek($position): ExtendedArray
    {
        parent::seek($position);

        return $this;
    }

    /**
     * Seek Key moves the pointer to given key
     *
     * @param int|string $key Property to seek
     *
     * @return ExtendedArray
     * @throws ExtendedArrayException
     */
    public function seekKey($key): ExtendedArray
    {
        if (!$this->offsetExists($key)) {
            throw new ExtendedArrayException("Key '{$key}' doesn't exist!");
        }

        $keyPosition = array_search(
            $key,
            $this->_positionMap,
            true
        );

        return $this->seek($keyPosition);
    }

    /**
     * Get Position Map
     *
     * @return array
     */
    protected function getPositionMap(): array
    {
        return $this->_positionMap;
    }

    /**
     * Save Current Cursor Position so it can be restored
     *
     * @return void
     */
    protected function saveCursor(): void
    {
        $this->_lastCursorKey = $this->key();
    }

    /**
     * Restore Cursor Position
     *
     * @return void
     */
    protected function restoreCursor(): void
    {
        if (!is_null($this->_lastCursorKey)) {
            $this->seekKey($this->_lastCursorKey);
        }
    }

    /**
     * Update Position Map
     *
     * @return null
     */
    protected function updatePositionMap(): void
    {
        $this->_positionMap = [];

        for ($this->first(); $this->valid(); $this->next()) {
            array_push($this->_positionMap, $this->key());
        }

        $this->rewind();
    }

    /**
     * Append Position Map
     *
     * @param int|string $keyName being appended
     *
     * @return null
     */
    private function _appendPositionMap($keyName = null): void
    {
        if (empty($keyName)) {
            $this->saveCursor();
            $keyName = $this->last()->key();
            $this->restoreCursor();
        }

        array_push($this->_positionMap, $keyName);
    }
}
