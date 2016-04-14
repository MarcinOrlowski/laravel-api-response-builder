<?php

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Config;

/**
 * ErrorCode handling class
 */
class ErrorCode
{
	/**
	 * protected code range - lowest code
	 */
	const _RESERVED_MIN_CODE = 0;

	/**
	 * protected code range - highest code
	 */
	const _RESERVED_MAX_CODE = 63;


	/**
	 * built-in codes: OK
	 */
	const OK = 0;
	/**
	 * built-in code for faillback message mapping
	 */
	const NO_ERROR_MESSAGE = 1;


	/**
	 * @var array built-in codes mapping
	 */
	protected static $base_map = [

		self::OK               => 'response-builder::builder.ok',
		self::NO_ERROR_MESSAGE => 'response-builder::builder.no_error_message_fmt',

	];


	/**
	 * Returns lowest allowed error code for this module
	 *
	 * @return integer
	 *
	 * @throws \RuntimeException Throws exception if no min_code set up
	 */
	protected static function getMinCode()
	{
		$min_code = Config::get('response_builder.min_code', null);

		if ($min_code === null) {
			throw new \RuntimeException('Missing min_code key in config/response_builder.php config file');
		}

		return $min_code;
	}

	/**
	 * Returns highest allowed error code for this module
	 *
	 * @return integer
	 *
	 * @throws \RuntimeException Throws exception if no max_code set up
	 */
	protected static function getMaxCode()
	{
		$max_code = Config::get('response_builder.max_code', null);

		if ($max_code === null) {
			throw new \RuntimeException('Missing min_code key in config/response_builder.php config file');
		}

		return $max_code;
	}


	/**
	 * Returns lowest possible reserved code used by predefined Response Builder's messages
	 *
	 * @return integer
	 */
	protected static function getReservedMinCode()
	{
		return static::_RESERVED_MIN_CODE;
	}

	/**
	 * Returns hihest possible reserved code used by predefined Response Builder's messages
	 *
	 * @return integer
	 */
	protected static function getReservedMaxCode()
	{
		return static::_RESERVED_MAX_CODE;
	}

	/**
	 * Returns array of error code constants defined in this class. Used mainly for debugging/tests
	 *
	 * @return array
	 */
	public static function getErrorCodeConstants()
	{
		$reflect = new \ReflectionClass(get_called_class());
		$constants = $reflect->getConstants();

		// filter out all internal constants (starting with underscore
		foreach ($constants as $name => $val) {
			if (substr($name, 0, 1) == '_') {
				unset($constants[ $name ]);
			}
		}

		return $constants;
	}

	/**
	 * Returns complete error code to locale string mapping array
	 *
	 * @return array
	 *
	 * @throws \RuntimeException Thrown when builder map is not configured.
	 */
	public static function getMap()
	{
		$map = Config::get('response_builder.map', null);
		if ($map === null) {
			throw new \RuntimeException('Missing "map" key in config/response_builder.php config file');
		}

		return $map + static::$base_map;
	}

	/**
	 * Returns locale mappings for given error code or null if there's no mapping
	 *
	 * @param integer $code Code to look for string mapping for.
	 *
	 * @return string|null
	 *
	 * @throws \InvalidArgumentException If $code is not in allowed range.
	 */
	public static function getMapping($code)
	{
		if (static::isCodeValid($code) === false) {
			throw new \InvalidArgumentException("Message code {$code} is out of allowed range");
		}

		$map = static::getMap();

		return array_key_exists($code, $map) ? $map[ $code ] : null;
	}

	/**
	 * Checks if given $code can is valid in this module and can be safely used
	 *
	 * @param integer $code Code to check
	 *
	 * @return boolean
	 */
	public static function isCodeValid($code)
	{
		$result = false;

		if (($code >= ErrorCode::getMinCode()) && ($code <= ErrorCode::getMaxCode())
			|| ($code <= ErrorCode::getReservedMaxCode()) && ($code >= ErrorCode::getReservedMinCode())
		) {
			$result = true;
		}

		return $result;
	}

}
