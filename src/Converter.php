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
	 * @var array
	 */
	protected $classes;

	/**
	 * Converter constructor.
	 */
	public function __construct()
	{
		$classes = Config::get(ResponseBuilder::CONF_KEY_CLASSES) ?? [];
		if (!is_array($classes)) {
			throw new \RuntimeException(
				sprintf('CONFIG: "classes" mapping must be an array (%s given)', gettype($classes)));
		}

		$this->classes = $classes;
	}

	/**
	 * Returns local copy of configuration mapping for the classes.
	 *
	 * @return array
	 */
	public function getClasses(): array
	{
		return $this->classes;
	}

	/**
	 * Checks if we have "classes" mapping configured for $data object class.
	 * Returns @true if there's valid config for this class.
	 *
	 * @param object $data Object to check mapping for.
	 *
	 * @return array
	 *
	 * @throws \RuntimeException if there's no config "classes" mapping entry
	 *                           for this object configured.
	 */
	protected function getClassMappingConfigOrThrow(object $data): array
	{
		$result = null;

		// check for exact class name match...
		$cls = get_class($data);
		if (array_key_exists($cls, $this->classes)) {
			$result = $this->classes[ $cls ];
		} else {
			// no exact match, then lets try with `instanceof`
			foreach (array_keys($this->classes) as $class_name) {
				if ($data instanceof $class_name) {
					$result = $this->classes[ $class_name ];
					break;
				}
			}
		}

		if ($result === null) {
			throw new \InvalidArgumentException(sprintf('No data conversion mapping configured for "%s" class.', $cls));
		}

		return $result;
	}

	/**
	 * We need to prepare source data
	 *
	 * @param null $data
	 *
	 * @return array|null
	 */
	public function convert($data = null): ?array
	{
		if ($data === null) {
			return null;
		}

		if (is_object($data)) {
			$cfg = $this->getClassMappingConfigOrThrow($data);
			$data = [$cfg[ ResponseBuilder::KEY_KEY ] => $data->{$cfg[ ResponseBuilder::KEY_METHOD ]}()];
		} elseif (!is_array($data)) {
			throw new \InvalidArgumentException(
				sprintf('Invalid payload data. Must be null, array or object with mapping ("%s" given).', gettype($data)));
		}

		return $this->convertArray($data);
	}

	/**
	 * Recursively walks $data array and converts all known objects if found. Note
	 * $data array is passed by reference so source $data array may be modified.
	 *
	 * @param array $data array to recursively convert known elements of
	 *
	 * @return array
	 */
	protected function convertArray(array $data): array
	{
		// This is to ensure that we either have array with user provided keys i.e. ['foo'=>'bar'], which will then
		// be turned into JSON object or array without user specified keys (['bar']) which we would return as JSON
		// array. But you can't mix these two as the final JSON would not produce predictable results.
		$string_keys_cnt = 0;
		$int_keys_cnt = 0;
		foreach ($data as $key => $val) {
			if (is_int($key)) {
				$int_keys_cnt++;
			} else {
				$string_keys_cnt++;
			}

			if (($string_keys_cnt > 0) && ($int_keys_cnt > 0)) {
				throw new \RuntimeException(
					'Invalid data array. Either set own keys for all the items or do not specify any keys at all. ' .
					'Arrays with mixed keys are not supported by design.');
			}
		}

		foreach ($data as $key => $val) {
			if (is_array($val)) {
				$data[ $key ] = $this->convertArray($val);
			} elseif (is_object($val)) {
				$cls = get_class($val);
				if (array_key_exists($cls, $this->classes)) {
					$conversion_method = $this->classes[ $cls ][ ResponseBuilder::KEY_METHOD ];
					$converted_data = $val->$conversion_method();
					$data[ $key ] = $converted_data;
				}
			}
		}

		return $data;
	}
}
