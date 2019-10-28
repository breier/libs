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
                return $b <=> $a;
            }
        );

        $this->updatePositionMap();
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
        $compare = $strict
            ? function ($a, $b) {
                return $a === $b;
            }
            : function ($a, $b) {
                return (object) $a == (object) $b;
            };

        $isContained = false;

        $this->saveCursor();

        foreach ($this as $element) {
            if ($compare($element, $needle)) {
                $isContained = true;
                break;
            }
        }

        $this->restoreCursor();

        return $isContained;
    }

    /**
     * Filter, poly-fill for `array_filter`
     *
     * @param callable $callback Function to use
     *
     * @return ExtendedArray
     */
    public function filter(callable $callback = null): ExtendedArray
    {
        if (is_null($callback)) {
            $callback = function ($item) {
                return !empty($item);
            };
        }

        $this->saveCursor();

        $filteredArray = new static();

        foreach ($this as $key => $value) {
            if ($callback($value)) {
                $filteredArray->offsetSet($key, $value);
            }
        }

        $this->restoreCursor();

        return $filteredArray;
    }

    /**
     * ExtendedArray from JSON
     *
     * @param string $json    To parse
     * @param int    $depth   Recursion level
     * @param int    $options (JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING | ...)
     *
     * @return ExtendedArray
     */
    public static function fromJSON(
        string $json,
        int $depth = 512,
        int $options = 0
    ): ExtendedArray {
        return new static(
            json_decode($json, true, $depth, $options)
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
     * JSON Serialize
     *
     * @param int $options (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | ...)
     * @param int $depth   Recursion level
     *
     * @return string
     */
    public function jsonSerialize(int $options = 0, $depth = 512): string
    {
        return json_encode($this, $options, $depth);
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
                if (is_numeric($b) ^ is_numeric($a)) {
                    return is_numeric($b) <=> is_numeric($a);
                }

                return $b <=> $a;
            }
        );

        $this->updatePositionMap();
    }

    /**
     * Map, poly-fill for `array_map`
     *
     * @param callable $callback Function to use
     *
     * @return ExtendedArray
     */
    public function map(callable $callback): ExtendedArray
    {
        $this->saveCursor();

        $mappedArray = new static();

        foreach ($this as $key => $value) {
            $mappedArray->offsetSet($key, $callback($value));
        }

        $this->restoreCursor();

        return $mappedArray;
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

        return is_array($firstItem)
            ? new static($firstItem)
            : $firstItem;
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

        $this->updatePositionMap();
    }
}
