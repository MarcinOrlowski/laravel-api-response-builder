<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Converters;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\Contracts\ConverterContract;
use MarcinOrlowski\ResponseBuilder\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

/**
 * Converts JsonSerializable to array
 */
final class JsonSerializableConverter implements ConverterContract
{
	/**
	 * Returns array representation of the object implementing \JsonSerializable interface.
	 *
	 * @param object $obj               Object to be converted
	 * @param array  $config            Converter config array to be used for this object (based on exact class
	 *                                  name match or inheritance).
	 */
	public function convert(object $obj, array $config): array
	{
		Validator::assertInstanceOf('obj', $obj, \JsonSerializable::class);

        /** @var \JsonSerializable $obj */
		$encoded = \json_encode($obj->jsonSerialize());
		if ($encoded === false) {
			$encoded = '';
		}

		return [$config[ RB::KEY_KEY ] => \json_decode($encoded, true)];
	}

} // end of class
