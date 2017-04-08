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
 * BaseApiCodes handling class
 */
class BaseApiCodes
{
	use ApiCodesHelpers;


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
	 * built-in error code for \Illuminate\Auth\AuthenticationException
	 */
	const EX_AUTHENTICATION_EXCEPTION = 14;


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
		self::EX_AUTHENTICATION_EXCEPTION => 'response-builder::builder.authentication_exception',
	];

	// ---------------------------------------------

	/**
	 * Returns base code mapping array
	 *
	 * @return array
	 */
	public static function getBaseMap()
	{
		return static::$base_map;
	}

}
