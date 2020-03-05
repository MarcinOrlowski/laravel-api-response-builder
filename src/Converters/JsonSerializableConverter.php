<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Converters;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2020 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\Contracts\ConverterContract;
use MarcinOrlowski\ResponseBuilder\Validator;

class JsonSerializableConverter implements ConverterContract
{
    /**
     * Returns array representation of the object implementing \JsonSerializable interface.
     *
     * @param \JsonSerializable $obj    Object to be converted
     * @param array             $config Converter config array to be used for this object (based on exact class
     *                                  name match or inheritance).
     *
     * @return array
     */
    public function convert($obj, array /** @scrutinizer ignore-unused */ $config): array
    {
        Validator::assertInstanceOf('obj', $obj, \JsonSerializable::class);

        return ['val' => json_decode($obj->jsonSerialize(), true)];
    }
}
