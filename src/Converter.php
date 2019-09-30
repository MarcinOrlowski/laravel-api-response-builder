<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Config;


/**
 * Data converter
 */
class Converter
{
	/**
	 * Reads and validates "classes" config mapping
	 *
	 * @return array Classes mapping as specified in configuration or empty array if configuration found
	 *
	 * @throws \RuntimeException if "classes" mapping is technically invalid (i.e. not array etc).
	 */
	public static function getClassesMapping(): array
	{
		$classes = Config::get(ResponseBuilder::CONF_KEY_CLASSES) ?? [];
		if (!is_array($classes)) {
			throw new \RuntimeException(
				sprintf('CONFIG: "classes" mapping must be an array (%s given)', gettype($classes)));
		}

		return $classes;
	}

	/**
	 * Checks if we have "classes" mapping configured for $data object class.
	 * Returns @true if there's valid config for this class.
	 *
	 * @param object $data Object to check mapping for.
	 *
	 * @return bool
	 *
	 * @throws \InvalidArgumentException if $data is not an object.
	 */
	public static function hasClassesMapping(object $data): bool
	{
		$classes = static::getClassesMapping();

		// check for exact class name match...
		$result = array_key_exists(get_class($data), $classes);

		return $result;
	}

	/**
	 * Recursively walks $data array and converts all known objects if found. Note
	 * $data array is passed by reference so source $data array may be modified.
	 *
	 * @param array $classes "classes" config mapping array
	 * @param array $data    array to recursively convert known elements of
	 *
	 * @return void
	 */
	public static function convert(array $classes, array &$data): void
	{
		foreach ($data as $data_key => &$data_val) {
			if (is_array($data_val)) {
				static::convert($classes, $data_val);
			} elseif (is_object($data_val)) {
				$obj_class_name = get_class($data_val);
				if (array_key_exists($obj_class_name, $classes)) {
					$conversion_method = $classes[ $obj_class_name ][ ResponseBuilder::KEY_METHOD ];
					$converted = $data_val->$conversion_method();
					$data[ $data_key ] = $converted;
				}
			}
		}
	}
}
