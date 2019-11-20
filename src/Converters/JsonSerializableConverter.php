<?php
declare(strict_types=1);

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

class JsonSerializableConverter implements ConverterContract
{
    /**
     * @param \JsonSerializable $obj
     * @param array             $config
     *
     * @return array
     */
    public function convert($obj, array /** @scrutinizer ignore-unused */ $config): array
    {
        if (!($obj instanceof \JsonSerializable)) {
            throw new \RuntimeException('Expected instance of JsonSerializable, got ' . get_class($obj));
        }

        return ['val' => json_decode($obj->jsonSerialize(), true)];
    }
}
