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
 * BaseApiCodes handling class
 */
class BaseApiCodes
{
	use ApiCodesHelpers;

	/**
	 * protected code range - lowest code for reserved range.
	 */
	protected const RESERVED_MIN_API_CODE_OFFSET = 0;

	/**
	 * protected code range - highest code for reserved range
	 */
	protected const RESERVED_MAX_API_CODE_OFFSET = 19;

	/**
	 * built-in codes: OK
	 */
	protected const OK_OFFSET = 0;
	/**
	 * built-in code for fallback message mapping
	 */
	protected const NO_ERROR_MESSAGE_OFFSET = 1;
	/**
	 * built-in error code for HTTP_NOT_FOUND exception
	 */
	protected const EX_HTTP_NOT_FOUND_OFFSET = 10;
	/**
	 * built-in error code for HTTP_SERVICE_UNAVAILABLE exception
	 */
	protected const EX_HTTP_SERVICE_UNAVAILABLE_OFFSET = 11;
	/**
	 * built-in error code for HTTP_EXCEPTION
	 */
	protected const EX_HTTP_EXCEPTION_OFFSET = 12;
	/**
	 * built-in error code for UNCAUGHT_EXCEPTION
	 */
	protected const EX_UNCAUGHT_EXCEPTION_OFFSET = 13;

	/**
	 * built-in error code for \Illuminate\Auth\AuthenticationException
	 */
	protected const EX_AUTHENTICATION_EXCEPTION_OFFSET = 14;

	/**
	 * built-in error code for \Illuminate\Auth\AuthenticationException
	 */
	protected const EX_VALIDATION_EXCEPTION_OFFSET = 15;


	/**
	 * Returns base code mapping array
	 *
	 * @return array
	 */
	protected static function getBaseMap(): array
	{
		/**
		 * @var array built-in codes mapping
		 */
		return [
			self::OK()                          => 'response-builder::builder.ok',
			self::NO_ERROR_MESSAGE()            => 'response-builder::builder.no_error_message',
			self::EX_HTTP_NOT_FOUND()           => 'response-builder::builder.http_not_found',
			self::EX_HTTP_SERVICE_UNAVAILABLE() => 'response-builder::builder.http_service_unavailable',
			self::EX_HTTP_EXCEPTION()           => 'response-builder::builder.http_exception',
			self::EX_UNCAUGHT_EXCEPTION()       => 'response-builder::builder.uncaught_exception',
			self::EX_AUTHENTICATION_EXCEPTION() => 'response-builder::builder.authentication_exception',
			self::EX_VALIDATION_EXCEPTION()     => 'response-builder::builder.validation_exception',
		];
	}

	// ---------------------------------------------

	/**
	 * Returns API code for internal code OK
	 *
	 * @return int valid API code in current range
	 */
	public static function OK(): int
	{
		return static::getCodeForInternalOffset(static::OK_OFFSET);
	}

	/**
	 * Returns API code for internal code NO_ERROR_MESSAGE
	 *
	 * @return int valid API code in current range
	 */
	public static function NO_ERROR_MESSAGE(): int
	{
		return static::getCodeForInternalOffset(static::NO_ERROR_MESSAGE_OFFSET);
	}

	/**
	 * Returns API code for internal code EX_HTTP_NOT_FOUND
	 *
	 * @return int valid API code in current range
	 */
	public static function EX_HTTP_NOT_FOUND(): int
	{
		return static::getCodeForInternalOffset(static::EX_HTTP_NOT_FOUND_OFFSET);
	}

	/**
	 * Returns API code for internal code EX_HTTP_EXCEPTION
	 *
	 * @return int valid API code in current range
	 */
	public static function EX_HTTP_EXCEPTION(): int
	{
		return static::getCodeForInternalOffset(static::EX_HTTP_EXCEPTION_OFFSET);
	}

	/**
	 * Returns API code for internal code EX_UNCAUGHT_EXCEPTION
	 *
	 * @return int valid API code in current range
	 */
	public static function EX_UNCAUGHT_EXCEPTION(): int
	{
		return static::getCodeForInternalOffset(static::EX_UNCAUGHT_EXCEPTION_OFFSET);
	}

	/**
	 * Returns API code for internal code EX_AUTHENTICATION_EXCEPTION
	 *
	 * @return int valid API code in current range
	 */
	public static function EX_AUTHENTICATION_EXCEPTION(): int
	{
		return static::getCodeForInternalOffset(static::EX_AUTHENTICATION_EXCEPTION_OFFSET);
	}

	/**
	 * Returns API code for internal code EX_VALIDATION_EXCEPTION
	 *
	 * @return int valid API code in current range
	 */
	public static function EX_VALIDATION_EXCEPTION(): int
	{
		return static::getCodeForInternalOffset(static::EX_VALIDATION_EXCEPTION_OFFSET);
	}

	/**
	 * Returns API code for internal code EX_HTTP_SERVICE_UNAVAILABLE
	 *
	 * @return int valid API code in current range
	 */
	public static function EX_HTTP_SERVICE_UNAVAILABLE(): int
	{
		return static::getCodeForInternalOffset(static::EX_HTTP_SERVICE_UNAVAILABLE_OFFSET);
	}

	/**
	 * Returns response JSON key value. If there's user provided mapping, it takes
	 * that into account, otherwise fails to default mapping values.
	 *
	 * @param string $reference_key JSON response key name reference to look up
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	public static function getResponseKey($reference_key): string
	{
		/**
		 * Default response JSON key mapping
		 *
		 * @var array
		 */
		$response_key_map = [
			ResponseBuilder::KEY_SUCCESS => 'success',
			ResponseBuilder::KEY_CODE    => 'code',
			ResponseBuilder::KEY_LOCALE  => 'locale',
			ResponseBuilder::KEY_MESSAGE => 'message',
			ResponseBuilder::KEY_DATA    => 'data',
		];

		// ensure $key is known
		if (!array_key_exists($reference_key, $response_key_map)) {
			throw new \RuntimeException(sprintf('Unknown response key reference "%s"', $reference_key));
		}

		return $response_key_map[ $reference_key ];
	}

}
