<?php
/**
 * PHP Version 7
 *
 * Extended Array Merge Map Class
 *
 * @category Extended_Class
 * @package  Breier\Libs
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     none.io
 */

namespace Breier\ExtendedArray;

/**
 * Extended Array Merge Map Class
 *
 * @category Extended_Class
 * @package  Breier\Libs
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     none.io
 */
class ExtendedArrayMergeMap
{
    protected $elements = [];

    /**
     * Instantiate an Extended Array Merge Map
     *
     * @param mixed $element To be stored
     * @param mixed $extra   To be merged
     */
    public function __construct($element, $extra = null)
    {
        $this->merge($element);
        
        if (null !== $extra) {
            $this->merge($extra);
        }
    }

    /**
     * Get Elements
     *
     * @return array
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * Merge element to this
     *
     * @param mixed $element To be merged
     *
     * @return ExtendedArrayMergeMap
     */
    public function merge($element): ExtendedArrayMergeMap
    {
        array_push($this->elements, $element);

        return $this;
    }

    /**
     * Merge Push adds values from another array into main.
     *
     * @param ExtendedArray $mainArray To merge to
     * @param array         $array     To merge from
     *
     * @return null
     */
    public static function mergePush(ExtendedArray $mainArray, array $array): void
    {
        $tempArray = new ExtendedArray($array);

        for (
            $mainArray->first(), $tempArray->first();
            $mainArray->valid(), $tempArray->valid();
            $mainArray->next(), $tempArray->next()
        ) {
            if (! $mainArray->element() instanceof static) {
                $mainArray->offsetSet(
                    $mainArray->key(),
                    new static($mainArray->element(), $tempArray->element())
                );
                continue;
            }

            $mainArray->offsetSet(
                $mainArray->key(),
                $mainArray->element()->merge($tempArray->element())
            );
        }
    }

    /**
     * Prepare Map Params
     *
     * @param ExtendedArray $mainArray To map to
     * @param array         $params    To be mapped
     *
     * @return ExtendedArray
     */
    public static function prepareMapParams(
        ExtendedArray $mainArray,
        array $params = null
    ): ExtendedArray {
        $preparedParams = $mainArray->values();

        foreach ($params as $array) {
            static::mergePush($preparedParams, $array);
        }

        foreach ($preparedParams as &$element) {
            if (! $element instanceof static) {
                $element = new static($element);
            }
        }

        return $preparedParams;
    }
}
