<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Converters;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2021 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\Contracts\ConverterContract;
use MarcinOrlowski\ResponseBuilder\Validator;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;

/**
 * Generic object-to-array array converter.
 */
final class ToArrayConverter implements ConverterContract
{
	/**
	 * Returns array representation of the object.
	 *
	 * @param object $obj    Object to be converted
	 * @param array  $config Converter config array to be used for this object (based on exact class
	 *                       name match or inheritance).
	 *
	 * @return array
	 *
	 * @throws Ex\InvalidTypeException
	 *
	 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceAfterLastUsed
	 */
    public function convert(object $obj, array $config): array
    {
        Validator::assertIsObject('obj', $obj);

        return $obj->toArray(null);
    }
}
