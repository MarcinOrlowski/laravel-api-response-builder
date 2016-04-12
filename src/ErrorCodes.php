<?php

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Config;

/**
 * ErrorCode handling class
 *
 * @package MarcinOrlowski\ResponseBuilder
 */
class ErrorCodes
{
	// protected code range
	const _RESERVED_MIN_CODE =  0;
	const _RESERVED_MAX_CODE = 63;

	// built-in codes
	const OK                   = 0;
	const NO_ERROR_MESSAGE     = 1;

	// built-in codes mapping
	protected static $base_map = [

		self::OK               => 'response_builder.ok',
		self::NO_ERROR_MESSAGE => 'response_builder.no_error_message_fmt',

	];


	/**
	 * Returns lowest allowed error code for this module
	 *
	 * @return int
	 */
	protected static function getMinCode() {
		$min_code = Config::get('response_builder.min_code', null);

		if($min_code === null) {
			throw new \RuntimeException('Missing min_code key in config/response_builder.php config file');
		}

		return $min_code;
	}

	/**
	 * Returns highest allowed error code for this module
	 *
	 * @return int
	 */
	protected static function getMaxCode() {
		$max_code = Config::get('response_builder.max_code', null);

		if($max_code === null) {
			throw new \RuntimeException('Missing min_code key in config/response_builder.php config file');
		}

		return $max_code;
	}


	/**
	 * Returns lowest possible reserved code used by predefined Response Builder's messages
	 *
	 * @return int
	 */
	protected static function getReservedMinCode() {
		return static::_RESERVED_MIN_CODE;
	}

	/**
	 * Returns hihest possible reserved code used by predefined Response Builder's messages
	 *
	 * @return int
	 */
	protected static function getReservedMaxCode() {
		return static::_RESERVED_MAX_CODE;
	}

	/**
	 * Returns array of error code constants defined in this class. Used mainly for debugging/tests
	 *
	 * @return array
	 */
	public static function getErrorCodeConstants() {
		$reflect = new \ReflectionClass(get_called_class());
		$constants = $reflect->getConstants();

		// filter out all internal constants (starting with underscore
		foreach($constants as $name=>$val) {
			if(substr($name,0,1) == '_') {
				unset($constants[$name]);
			}
		}

		return $constants;
	}

	/**
	 * Returns complete error code to locale string mapping array
	 *
	 * @return array
	 */
	public static function getMap() {
		$map = Config::get('response_builder.map', null);

		if($map === null) {
			throw new \RuntimeException('Missing min_code key in config/response_builder.php config file');
		}

		return $map + static::$base_map;
	}

	/**
	 * Returns locale mappings for given error code or null if there's no mapping
	 *
	 * @param int $code
	 *
	 * @return string|null
	 */
	public static function getMapping($code) {
		if( static::isCodeValid($code) === false ) {
			throw new \InvalidArgumentException("Message code {$code} is out of allowed range");
		}

		$map = static::getMap();
		return array_key_exists($code, $map) ? $map[$code] : null;
	}

	/**
	 * Checks if given $code can is valid in this module and can be safely used
	 *
	 * @param int $code
	 *
	 * @return bool
	 */
	public static function isCodeValid($code) {
		$result = false;

		if( ($code >= ErrorCodes::getMinCode()) && ($code <= ErrorCodes::getMaxCode())
			|| ($code <= ErrorCodes::getReservedMaxCode()) && ($code >= ErrorCodes::getReservedMinCode())
		) {
			$result = true;
		}

		return $result;
	}

}
