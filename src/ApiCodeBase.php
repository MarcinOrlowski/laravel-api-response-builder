<?php

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Config;

/**
 * ApiCode handling class
 */
class ApiCodeBase
{
	/**
	 * protected code range - lowest code
	 */
	const RESERVED_MIN_API_CODE = 0;

	/**
	 * protected code range - highest code
	 */
	const RESERVED_MAX_API_CODE = 63;


	/**
	 * built-in codes: OK
	 */
	const OK = 0;
	/**
	 * built-in code for fallback message mapping
	 */
	const NO_ERROR_MESSAGE = 1;
	/**
	 * built-in error code for HTTP_NOT_FOUND exception
	 */
	const EX_HTTP_NOT_FOUND = 10;
	/**
	 * built-in error code for HTTP_SERVICE_UNAVAILABLE exception
	 */
	const EX_HTTP_SERVICE_UNAVAILABLE = 11;
	/**
	 * built-in error code for HTTP_EXCEPTION
	 */

	const EX_HTTP_EXCEPTION = 12;
	/**
	 * built-in error code for UNCAUGHT_EXCEPTION
	 */
	const EX_UNCAUGHT_EXCEPTION = 13;


	/**
	 * @var array built-in codes mapping
	 */
	protected static $base_map = [
		self::OK               => 'response-builder::builder.ok',
		self::NO_ERROR_MESSAGE => 'response-builder::builder.no_error_message',

		self::EX_HTTP_NOT_FOUND           => 'response-builder::builder.http_not_found',
		self::EX_HTTP_SERVICE_UNAVAILABLE => 'response-builder::builder.http_service_unavailable',
		self::EX_HTTP_EXCEPTION           => 'response-builder::builder.http_exception',
		self::EX_UNCAUGHT_EXCEPTION       => 'response-builder::builder.uncaught_exception',
	];


	/**
	 * Returns lowest allowed error code for this module
	 *
	 * @return integer
	 *
	 * @throws \RuntimeException Throws exception if no min_code set up
	 */
	public static function getMinCode()
	{
		$min_code = Config::get('response_builder.min_code', null);

		if ($min_code === null) {
			throw new \RuntimeException('CONFIG: Missing "min_code" key');
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
	public static function getMaxCode()
	{
		$max_code = Config::get('response_builder.max_code', null);

		if ($max_code === null) {
			throw new \RuntimeException('CONFIG: Missing "max_code" key');
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
		return static::RESERVED_MIN_API_CODE;
	}

	/**
	 * Returns highest possible reserved code used by predefined Response Builder's messages
	 *
	 * @return integer
	 */
	protected static function getReservedMaxCode()
	{
		return static::RESERVED_MAX_API_CODE;
	}

	/**
	 * Returns array of error code constants defined in this class. Used mainly for debugging/tests
	 *
	 * @return array
	 */
	public static function getApiCodeConstants()
	{
		$reflect = new \ReflectionClass(get_called_class());
		return $reflect->getConstants();
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
			throw new \RuntimeException('CONFIG: Missing "map" key');
		}

		if (!is_array($map)) {
			throw new \RuntimeException('CONFIG: "map" must be an array');
		}

		/** @noinspection AdditionOperationOnArraysInspection */
		return $map + static::$base_map;
	}

	/**
	 * Returns locale mappings for given base error code or @null if there's no mapping
	 *
	 * @param integer $code Base (built-in) code to look for mapped message for.
	 *
	 * @return string|null
	 *
	 * @throws \InvalidArgumentException If $code is not in allowed reserved range.
	 */
	public static function getBaseMapping($code)
	{
		if (($code < ApiCodeBase::RESERVED_MIN_API_CODE) || ($code > ApiCodeBase::RESERVED_MAX_API_CODE)) {
			throw new \InvalidArgumentException(
				sprintf('Base code value (%d) is out of allowed reserved range %d-%d',
					$code, ApiCodeBase::RESERVED_MIN_API_CODE, ApiCodeBase::RESERVED_MAX_API_CODE));
		}

		return array_key_exists($code, static::$base_map)
			? static::$base_map[ $code ]
			: null;
	}


	/**
	 * Returns locale mappings for given error code or @null if there's no mapping
	 *
	 * @param integer $api_code Api code to look for mapped message for.
	 *
	 * @return string|null
	 *
	 * @throws \InvalidArgumentException If $code is not in allowed range.
	 */
	public static function getMapping($api_code)
	{
		if (!static::isCodeValid($api_code)) {
			$msg = sprintf('API code value (%d) is out of allowed range %d-%d',
				$api_code, static::getMinCode(), static::getMaxCode());
			throw new \InvalidArgumentException($msg);
		}

		$map = static::getMap();

		return array_key_exists($api_code, $map) ? $map[ $api_code ] : null;
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

		if ((($code >= ApiCodeBase::getMinCode()) && ($code <= ApiCodeBase::getMaxCode()))
			|| (($code <= ApiCodeBase::getReservedMaxCode()) && ($code >= ApiCodeBase::getReservedMinCode()))
		) {
			$result = true;
		}

		return $result;
	}

}
