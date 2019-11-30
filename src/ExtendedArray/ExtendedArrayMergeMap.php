<?php

/**
 * PHP Version 7
 *
 * Extended Array Merge Map File
 *
 * @category Extended_Class
 * @package  Breier\Libs
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Breier\ExtendedArray;

/**
 * Extended Array Merge Map Class
 */
class ExtendedArrayMergeMap
{
    protected $elements = [];

    /**
     * Instantiate an Extended Array Merge Map
     *
     * @param mixed ...$elements To be stored
     */
    public function __construct(...$elements)
    {
        foreach ($elements as $item) {
            $this->merge($item);
        }
    }

    /**
     * Get Array Copy
     */
    public function getArrayCopy(): array
    {
        return $this->elements;
    }

    /**
     * Merge element to this
     *
     * @param mixed $element To be merged
     */
    public function merge($element): ExtendedArrayMergeMap
    {
        array_push($this->elements, $element);

        return $this;
    }

    /**
     * Merge Push adds values from another array into main.
     */
    public static function mergePush(ExtendedArray $target, array $source): void
    {
        $extendedSource = new ExtendedArray($source);

        for (
            $target->first(), $extendedSource->first();
            $target->valid();
            $target->next(), $extendedSource->next()
        ) {
            if (! $target->element() instanceof static) {
                $target->offsetSet(
                    $target->key(),
                    new static($target->element(), $extendedSource->element())
                );
                continue;
            }

            $target->offsetSet(
                $target->key(),
                $target->element()->merge($extendedSource->element())
            );
        }
    }

    /**
     * Prepare Map Params
     */
    public static function prepareMapParams(
        ExtendedArray $mainArray,
        array $params = []
    ): ExtendedArray {
        $preparedParams = $mainArray->values();

        foreach ($params as $subParams) {
            if (!ExtendedArray::isArray($subParams)) {
                throw new \InvalidArgumentException(
                    'Second parameter has to be an array of arrays!'
                );
            }
            static::mergePush($preparedParams, $subParams);
        }

        foreach ($preparedParams as &$element) {
            if (! $element instanceof static) {
                $element = new static($element);
            }
        }

        return $preparedParams;
    }
}
