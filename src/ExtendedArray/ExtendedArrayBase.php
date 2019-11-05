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
 * @method bool offsetExists(mixed $index); Validate element index
 * @method mixed offsetGet(mixed $index); Get element in given index
 * @method string serialize(); Applies PHP serialization to the object
 * @method null setFlags(string $flags); Set behaviour flags of the ArrayIterator
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
    private $_positionMap = [];
    private $_lastCursorPosition = 0;

    /**
     * Instantiate an Extended Array
     *
     * @param mixed $array To be parsed into properties
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

        $this->_updatePositionMap();

        $this->rewind();
    }

    /**
     * Converts the Extended Array to JSON String
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->jsonSerialize();
    }

    /**
     * Extending ASort Method to support sub-arrays
     * Sort ascending by elements
     *
     * @return ExtendedArrayBase
     */
    public function asort(): ExtendedArrayBase
    {
        $this->uasort(
            function ($a, $b) {
                if (static::isArrayObject($a)) {
                    $a = $a->getArrayCopy();
                }
                if (static::isArrayObject($b)) {
                    $b = $b->getArrayCopy();
                }
                return $a < $b ? -1 : 1;
            }
        );

        return $this->rewind();
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
     * @return ExtendedArrayBase
     */
    public function end(): ExtendedArrayBase
    {
        if ($this->count()) {
            $this->seek($this->count() -1);
        }

        return $this;
    }

    /**
     * First is an alias for Rewind
     *
     * @return ExtendedArrayBase
     */
    public function first(): ExtendedArrayBase
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
            if (self::isArrayObject($item)) {
                $item = $item->getArrayCopy();
            }
            if ($item instanceof ExtendedArrayMergeMap) {
                $item = $item->getElements();
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
     * JSON Serialize
     *
     * @param int $options (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | ...)
     * @param int $depth   Recursion level
     *
     * @return string
     */
    public function jsonSerialize(int $options = 0, int $depth = 512): string
    {
        return json_encode($this, $options, $depth);
    }

    /**
     * Get Keys have to be implemented
     *
     * @return mixed
     */
    abstract public function keys();

    /**
     * Extending KSort Method to update position map
     * Sort ascending by element indexes
     *
     * @return ExtendedArrayBase
     */
    public function ksort(): ExtendedArrayBase
    {
        parent::ksort();

        $this->_updatePositionMap();

        return $this->rewind();
    }

    /**
     * Last is an alias to End
     *
     * @return ExtendedArrayBase
     */
    public function last(): ExtendedArrayBase
    {
        return $this->end();
    }

    /**
     * Extending NatCaseSort Method to update position map
     * Sort elements using case insensitive "natural order"
     *
     * @return ExtendedArrayBase
     */
    public function natcasesort(): ExtendedArrayBase
    {
        parent::natcasesort();

        $this->_updatePositionMap();

        return $this->rewind();
    }

    /**
     * Extending NatSort Method to update position map
     * Sort elements using "natural order"
     *
     * @return ExtendedArrayBase
     */
    public function natsort(): ExtendedArrayBase
    {
        parent::natsort();

        $this->_updatePositionMap();

        return $this->rewind();
    }

    /**
     * Extending next Method to return ExtendedArrayBase instead of void
     *
     * @return ExtendedArrayBase
     */
    public function next(): ExtendedArrayBase
    {
        parent::next();

        return $this;
    }

    /**
     * Extending Offset Set Method to update position map
     * Set an element with index name
     *
     * @param int|string $index  Key of the item
     * @param mixed      $newval To be set
     *
     * @return null
     */
    public function offsetSet($index, $newval): void
    {
        $isAppend = !is_null($index)
            ? !$this->offsetExists($index)
            : true;

        if (is_array($newval)
            || self::isArrayObject($newval)
            || $newval instanceof SplFixedArray
        ) {
            $newval = new static($newval);
        }

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

        $this->saveCursor();
        $this->_updatePositionMap();
        $this->restoreCursor();
    }

    /**
     * Move the Cursor to Previous element
     *
     * @return ExtendedArrayBase
     */
    public function prev(): ExtendedArrayBase
    {
        $currentPosition = $this->_getCursorPosition();

        if (!$currentPosition) {
            return $this->end()->next();
        }

        $this->seek($currentPosition - 1);
        return $this;
    }

    /**
     * Extending Rewind Method to return ExtendedArrayBase instead of void
     * Move the cursor to initial position
     *
     * @return ExtendedArrayBase
     */
    public function rewind(): ExtendedArrayBase
    {
        parent::rewind();

        return $this;
    }

    /**
     * Extending UAsort Method to update position map
     * Sort by elements using given function
     *
     * @param callable $cmp_function to compare
     *
     * @return ExtendedArrayBase
     */
    public function uasort($cmp_function): ExtendedArrayBase
    {
        parent::uasort($cmp_function);

        $this->_updatePositionMap();

        return $this->rewind();
    }

    /**
     * Extending UKsort Method to update position map
     * Sort by indexes using given function
     *
     * @param callable $cmp_function to compare
     *
     * @return ExtendedArrayBase
     */
    public function uksort($cmp_function): ExtendedArrayBase
    {
        parent::uksort($cmp_function);

        $this->_updatePositionMap();

        return $this->rewind();
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
        $this->_lastCursorPosition = $this->_getCursorPosition();
    }

    /**
     * Restore Cursor Position
     *
     * @return void
     */
    protected function restoreCursor(): void
    {
        if ($this->_lastCursorPosition >= $this->count()) {
            $this->end();
            return;
        }

        $this->seek($this->_lastCursorPosition);
    }

    /**
     * Get Cursor Position
     *
     * @return int
     */
    private function _getCursorPosition(): int
    {
        return array_search(
            $this->key(),
            $this->_positionMap,
            true
        );
    }

    /**
     * Update Position Map
     *
     * @return null
     */
    private function _updatePositionMap(): void
    {
        $this->_positionMap = [];

        for ($this->first(); $this->valid(); $this->next()) {
            array_push($this->_positionMap, $this->key());
        }
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
