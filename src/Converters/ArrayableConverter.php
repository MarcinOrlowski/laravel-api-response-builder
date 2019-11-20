<?php

namespace MarcinOrlowski\ResponseBuilder\Converters;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\Contracts\ConverterContract;
use \Illuminate\Contracts\Support\Arrayable;

class ArrayableConverter implements ConverterContract
{
    /**
     * Returns array representation of the object implementing Arrayable interface
     *
     * @param Arrayable $obj    Object to be converted
     * @param array     $config Converter config array to be used for this object (based on exact class
     *                          name match or inheritance).
     *
     * @return array
     */
    public function convert($obj, array /** @scrutinizer ignore-unused */ $config): array
    {
        return $obj->toArray();
    }
}
