<?php
/**
 * PHP Version 7
 *
 * Extended Array Class
 *
 * @category Extended_Class
 * @package  Breier\Libs
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     none.io
 */

namespace Breier\ExtendedArray;

use ArrayIterator;
use ArrayObject;
use SplFixedArray;

/**
 * Extended Array Class
 *
 * @category Extended_Class
 * @package  Breier\Libs
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     none.io
 */
class ExtendedArray extends ExtendedArrayBase
{
    /**
     * Reverse Sort by element, poly-fill for `arsort`
     *
     * @return void
     */
    public function arsort(): void
    {
        $this->uasort(
            function ($a, $b) {
                if (static::isArrayObject($a)) {
                    $a = $a->getArrayCopy();
                }
                if (static::isArrayObject($b)) {
                    $b = $b->getArrayCopy();
                }
                return $a < $b ? 1 : -1;
            }
        );
    }

    /**
     * Contains, poly-fill for `in_array`
     *
     * @param mixed $needle To search for
     * @param bool  $strict Hard or soft comparison
     *
     * @return bool
     *
     * @TO-DO review once array_search is implemented
     */
    public function contains($needle, $strict = false): bool
    {
        $compare = $this->_getCompareFunction($strict);
        $isContained = false;

        $this->saveCursor();

        foreach ($this as $element) {
            if (call_user_func_array($compare, [$element, $needle])) {
                $isContained = true;
                break;
            }
        }

        $this->restoreCursor();

        return $isContained;
    }

    /**
     * Filter, poly-fill for `array_filter`
     * When using flag "both", $value goes first, then $key.
     *
     * @param callable $callback Function to use
     * @param int      $flag     (ARRAY_FILTER_USE_KEY, ARRAY_FILTER_USE_BOTH)
     *
     * @return ExtendedArray
     */
    public function filter(callable $callback = null, int $flag = 0): ExtendedArray
    {
        if (is_null($callback)) {
            $flag = 0;
            $callback = function ($value) {
                return !empty($value);
            };
        }

        return new static(
            call_user_func_array(
                "array_filter",
                [$this->getArrayCopy(), $callback, $flag]
            )
        );
    }

    /**
     * ExtendedArray from JSON
     *
     * @param string $json  To parse
     * @param int    $depth Recursion level
     *
     * @return ExtendedArray
     * @throws JsonException
     */
    public static function fromJSON(string $json, int $depth = 512): ExtendedArray
    {
        return new static(
            json_decode($json, true, $depth, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Is Array static function, extends `is_array`
     *
     * @param array|ExtendedArray $element To be validated
     *
     * @return bool
     */
    public static function isArray($element): bool
    {
        return (
            is_array($element)
            || static::isArrayObject($element)
            || $element instanceof SplFixedArray
        );
    }

    /**
     * Extended Array Keys, poly-fill for `array_keys`
     *
     * @return ExtendedArray
     */
    public function keys(): ExtendedArray
    {
        return new static($this->getPositionMap());
    }

    /**
     * Reverse Sort by index, poly-fill for `krsort`
     *
     * @return void
     */
    public function krsort(): void
    {
        $this->uksort(
            function ($a, $b) {
                return $a < $b ? 1 : -1;
            }
        );
    }

    /**
     * Map, poly-fill for `array_map`
     *
     * @param callable $callback  Function to use
     * @param array    ...$params Extra parameters for the callback
     *
     * @return ExtendedArray
     */
    public function map(callable $callback, array ...$params): ExtendedArray
    {
        return new static(
            call_user_func_array(
                "array_map",
                array_merge([$callback, $this->getArrayCopy()], $params)
            )
        );
    }

    /**
     * Offset Get First
     *
     * @return mixed
     */
    public function offsetGetFirst()
    {
        $this->saveCursor();

        $firstItem = $this->first()->element();

        $this->restoreCursor();

        return $firstItem;
    }

    /**
     * Offset Get Last
     *
     * @return mixed
     */
    public function offsetGetLast()
    {
        $this->saveCursor();

        $lastItem = $this->last()->element();

        $this->restoreCursor();

        return $lastItem;
    }

    /**
     * Offset Get by given Position
     *
     * @param int $position To seek
     *
     * @return mixed
     */
    public function offsetGetPosition(int $position)
    {
        $this->saveCursor();

        $item = $this->seek($position)->element();

        $this->restoreCursor();

        return $item;
    }

    /**
     * Shuffle Elements Randomly, poly-fill for `shuffle`
     *
     * @return void
     */
    public function shuffle(): void
    {
        $this->uasort(
            function ($a, $b) {
                return rand(-1, 1);
            }
        );
    }

    /**
     * Get Compare Function
     *
     * @param bool $strict or not
     *
     * @return callable
     */
    private function _getCompareFunction(bool $strict = false): callable
    {
        if ($strict) {
            return function ($a, $b) {
                return $a === $b;
            };
        }

        return function ($a, $b) {
            return (object) $a == (object) $b;
        };
    }
}
