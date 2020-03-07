<?php

/**
 * PHP Version 7
 *
 * Extended Array File
 *
 * @category Extended_Class
 * @package  Breier\Libs
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Breier\ExtendedArray;

use Breier\ExtendedArray\ExtendedArrayMergeMap as MergeMap;

/**
 * Extended Array Class
 */
class ExtendedArray extends ExtendedArrayBase
{
    /**
     * Reverse Sort by element, poly-fill for `arsort`
     */
    public function arsort(): ExtendedArray
    {
        return $this->uasort(
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
     * @TO-DO review once array_search is implemented
     */
    public function contains($needle, bool $strict = false): bool
    {
        $compare = $this->getCompareFunction($strict);
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
     * Diff, poly-fill for `array_diff`
     */
    public function diff($array2, ...$arrays)
    {
        if (!static::isArray($array2)) {
            throw new \InvalidArgumentException(
                'Only array types are accepted as parameter!'
            );
        }

        $diffArrays = new static([$array2]);
        if (!empty($arrays)) {
            foreach ($arrays as $element) {
                $diffArrays->append($element);
            }
        }

        $resultingArray = new static($this);

        foreach ($diffArrays as $diff) {
            $resultingArray = $resultingArray->filterWithObjects(
                function ($value) use ($diff) {
                    return !$diff->contains($value);
                }
            );
        }

        return $resultingArray;
    }

    /**
     * Explode a string by delimiter
     */
    public static function explode(
        string $delimiter,
        string $string,
        int $limit = PHP_INT_MAX
    ): ExtendedArray {
        return new static(explode($delimiter, $string, $limit));
    }

    /**
     * Filter, poly-fill for `array_filter`
     * When using flag "both", $value goes first, then $key.
     *
     * @param callable $callback Function to use
     * @param int      $flag     (ARRAY_FILTER_USE_KEY, ARRAY_FILTER_USE_BOTH)
     */
    public function filter(
        callable $callback = null,
        int $flag = 0
    ): ExtendedArray {
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
     * Extending Filter to support objects
     *
     * @param callable $callback Function to use
     * @param int      $flag     (ARRAY_FILTER_USE_KEY, ARRAY_FILTER_USE_BOTH)
     */
    public function filterWithObjects(
        callable $callback = null,
        int $flag = 0
    ): ExtendedArray {
        if (is_null($callback)) {
            $flag = 0;
            $callback = function ($value) {
                return !empty($value);
            };
        }

        $this->saveCursor();

        $filteredArray = new static();

        foreach ($this as $key => $value) {
            $params = ($flag !== ARRAY_FILTER_USE_BOTH)
                ? ($flag === ARRAY_FILTER_USE_KEY) ? [$key] : [$value]
                : [$value, $key];

            if (call_user_func_array($callback, $params)) {
                $filteredArray->offsetSet($key, $value);
            }
        }

        $this->restoreCursor();

        return $filteredArray;
    }

    /**
     * ExtendedArray from JSON
     *
     * @throws JsonException
     */
    public static function fromJSON(
        string $json,
        int $depth = 512
    ): ExtendedArray {
        return new static(
            json_decode($json, true, $depth, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Is Array static function, extends `is_array`
     *
     * @param mixed $element To be validated
     */
    public static function isArray($element): bool
    {
        return (
            is_array($element)
            || static::isArrayObject($element)
            || $element instanceof \SplFixedArray
        );
    }

    /**
     * Concatenate array values in a string separated by glue
     */
    public function implode(string $glue = ''): string
    {
        $this->saveCursor();
        $outputString = '';

        for ($this->first(); $this->valid(); $this->next()) {
            if (strlen($outputString)) {
                $outputString .= $glue;
            }
            $outputString .= $this->element();
        }

        $this->restoreCursor();

        return $outputString;
    }

    /**
     * Extended Array Keys, poly-fill for `array_keys`
     */
    public function keys(): ExtendedArray
    {
        return new static($this->getPositionMap());
    }

    /**
     * Reverse Sort by index, poly-fill for `krsort`
     */
    public function krsort(): ExtendedArray
    {
        return $this->uksort(
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
     * Extending Map to support objects
     *
     * @param callable $callback  Function to use
     * @param array    ...$params Extra params to callback
     */
    public function mapWithObjects(
        callable $callback,
        array ...$params
    ): ExtendedArray {
        $this->saveCursor();
        $preparedParams = MergeMap::prepareMapParams($this, $params);
        $mappedArray = new static();

        for (
            $this->first(), $preparedParams->first();
            $this->valid(), $preparedParams->valid();
            $this->next(), $preparedParams->next()
        ) {
            $mappedArray->offsetSet(
                $this->key(),
                call_user_func_array(
                    $callback,
                    $preparedParams->element()->getArrayCopy()
                )
            );
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
     * @return mixed
     */
    public function offsetGetPosition(int $position)
    {
        $this->saveCursor();

        $this->seek($position);
        $item = $this->element();

        $this->restoreCursor();

        return $item;
    }

    /**
     * Shuffle Elements Randomly, poly-fill for `shuffle`
     */
    public function shuffle(): ExtendedArray
    {
        return $this->uasort(
            function ($a, $b) {
                return rand(-1, 1);
            }
        );
    }

    /**
     * Get Values in a numbered key array, poly-fill for `array_values`
     */
    public function values(): ExtendedArray
    {
        $this->saveCursor();

        $valuesArray = new static();

        for ($this->first(); $this->valid(); $this->next()) {
            $valuesArray->append($this->element());
        }

        $this->restoreCursor();

        return $valuesArray;
    }

    /**
     * Get Compare Function
     */
    private function getCompareFunction(bool $strict = false): callable
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
