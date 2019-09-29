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
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;


/**
 * Builds standardized HttpResponse response object
 */
class ResponseBuilder
{
	/**
	 * Default HTTP code to be used with success responses
	 */
	public const DEFAULT_HTTP_CODE_OK = HttpResponse::HTTP_OK;

	/**
	 * Default HTTP code to be used with error responses
	 */
	public const DEFAULT_HTTP_CODE_ERROR = HttpResponse::HTTP_BAD_REQUEST;

	/**
	 * Min allowed HTTP code for errorXXX()
	 */
	public const ERROR_HTTP_CODE_MIN = 400;

	/**
	 * Max allowed HTTP code for errorXXX()
	 */
	public const ERROR_HTTP_CODE_MAX = 599;

	/**
	 * Configuration keys
	 */
	public const CONF_KEY_DEBUG_DEBUG_KEY        = 'response_builder.debug.debug_key';
	public const CONF_KEY_DEBUG_EX_TRACE_ENABLED = 'response_builder.debug.exception_handler.trace_enabled';
	public const CONF_KEY_DEBUG_EX_TRACE_KEY     = 'response_builder.debug.exception_handler.trace_key';
	public const CONF_KEY_MAP                    = 'response_builder.map';
	public const CONF_KEY_ENCODING_OPTIONS       = 'response_builder.encoding_options';
	public const CONF_KEY_CLASSES                = 'response_builder.classes';
	public const CONF_KEY_MIN_CODE               = 'response_builder.min_code';
	public const CONF_KEY_MAX_CODE               = 'response_builder.max_code';
	public const CONF_KEY_RESPONSE_KEY_MAP       = 'response_builder.map';

	/**
	 * Default keys to be used by exception handler while adding debug information
	 */
	public const KEY_DEBUG   = 'debug';
	public const KEY_TRACE   = 'trace';
	public const KEY_CLASS   = 'class';
	public const KEY_FILE    = 'file';
	public const KEY_LINE    = 'line';
	public const KEY_KEY     = 'key';
	public const KEY_METHOD  = 'method';
	public const KEY_SUCCESS = 'success';
	public const KEY_CODE    = 'code';
	public const KEY_LOCALE  = 'locale';
	public const KEY_MESSAGE = 'message';
	public const KEY_DATA    = 'data';

	/**
	 * Default key to be used by exception handler while processing ValidationException
	 * to return all the error messages
	 */
	public const KEY_MESSAGES = 'messages';

	/**
	 * Default JSON encoding options. Must be specified as final value (i.e. 271) and NOT
	 * exression i.e. `JSON_HEX_TAG|JSON_HEX_APOS|...` as such syntax is not yet supported
	 * by PHP.
	 *
	 * 271 = JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE
	 */
	public const DEFAULT_ENCODING_OPTIONS = 271;

	/**
	 * Reads and validates "classes" config mapping
	 *
	 * @return array Classes mapping as specified in configuration or empty array if configuration found
	 *
	 * @throws \RuntimeException if "classes" mapping is technically invalid (i.e. not array etc).
	 */
	protected static function getClassesMapping(): ?array
	{
		$classes = Config::get(self::CONF_KEY_CLASSES);

		if ($classes !== null) {
			if (!is_array($classes)) {
				throw new \RuntimeException(
					sprintf('CONFIG: "classes" mapping must be an array (%s given)', gettype($classes)));
			}

			$mandatory_keys = [
				static::KEY_KEY,
				static::KEY_METHOD,
			];
			foreach ($classes as $class_name => $class_config) {
				foreach ($mandatory_keys as $key_name) {
					if (!array_key_exists($key_name, $class_config)) {
						throw new \RuntimeException("CONFIG: Missing '{$key_name}' for '{$class_name}' class mapping");
					}
				}
			}
		} else {
			$classes = [];
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
	protected static function hasClassesMapping(object $data): bool
	{
		return array_key_exists(get_class($data), static::getClassesMapping());
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
	protected static function convert(array $classes, array &$data): void
	{
		foreach ($data as $data_key => &$data_val) {
			if (is_array($data_val)) {
				static::convert($classes, $data_val);
			} elseif (is_object($data_val)) {
				$obj_class_name = get_class($data_val);
				if (array_key_exists($obj_class_name, $classes)) {
					$conversion_method = $classes[ $obj_class_name ][ static::KEY_METHOD ];
					$converted = $data_val->$conversion_method();
					$data[ $data_key ] = $converted;
				}
			}
		}
	}

	/**
	 * Returns success
	 *
	 * @param object|array|null $data             payload to be returned as 'data' node, @null if none
	 * @param integer|null      $api_code         API code to be returned with the response or @null for default `OK` code
	 * @param array|null        $lang_args        arguments passed to Lang if message associated with API code uses placeholders
	 * @param integer|null      $http_code        HTTP return code to be set for this response or @null for default (200)
	 * @param integer|null      $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use
	 *                                            config's value or defaults
	 *
	 * @return HttpResponse
	 */
	public static function success($data = null, $api_code = null, array $lang_args = null,
	                               int $http_code = null, int $encoding_options = null): HttpResponse
	{
		return static::buildSuccessResponse($data, $api_code, $lang_args, $http_code, $encoding_options);
	}

	/**
	 * Returns success
	 *
	 * @param integer|null $api_code  API code to be returned with the response or @null for default `OK` code
	 * @param array|null   $lang_args arguments passed to Lang if message associated with API code uses placeholders
	 * @param integer|null $http_code HTTP return code to be set for this response or @null for default (200)
	 *
	 * @return HttpResponse
	 */
	public static function successWithCode(int $api_code = null, array $lang_args = null, int $http_code = null): HttpResponse
	{
		return static::success(null, $api_code, $lang_args, $http_code);
	}

	/**
	 * Returns success with custom HTTP code
	 *
	 * @param integer|null $http_code HTTP return code to be set for this response. If @null is passed, falls back
	 *                                to DEFAULT_HTTP_CODE_OK.
	 *
	 * @return HttpResponse
	 */
	public static function successWithHttpCode(int $http_code = null): HttpResponse
	{
		return static::buildSuccessResponse(null, BaseApiCodes::OK(), [], $http_code);
	}

	/**
	 * @param object|array|null $data             payload to be returned as 'data' node, @null if none
	 * @param integer|null      $api_code         API code to be returned with the response or @null for `OK` code
	 * @param array|null        $lang_args        arguments passed to Lang if message associated with API code uses placeholders
	 * @param integer|null      $http_code        HTTP return code to be set for this response
	 * @param integer|null      $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use
	 *                                            config's value or defaults
	 *
	 * @return HttpResponse
	 *
	 * @throws \InvalidArgumentException Thrown when provided arguments are invalid.
	 */
	protected static function buildSuccessResponse($data = null, int $api_code = null, array $lang_args = null,
	                                               int $http_code = null, int $encoding_options = null): HttpResponse
	{
		$http_code = $http_code ?? static::DEFAULT_HTTP_CODE_OK;
		$api_code = $api_code ?? BaseApiCodes::OK();

		Validator::assertInt('api_code', $api_code);
		Validator::assertInt('http_code', $http_code);
		Validator::assertIntRange('http_code', $http_code, 200, 299);

		return static::make(true, $api_code, $api_code, $data, $http_code, $lang_args, null, $encoding_options);
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error
	 * message uses placeholders as well as return data payload
	 *
	 * @param integer           $api_code         API code to be returned with the response
	 * @param array|null        $lang_args        arguments array passed to Lang::get() for messages with placeholders
	 * @param object|array|null $data             payload array to be returned in 'data' node or response object
	 * @param integer|null      $http_code        optional HTTP status code to be used with this response or @null for default
	 * @param integer|null      $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use
	 *                                            config's value or defaults
	 *
	 * @return HttpResponse
	 */
	public static function error(int $api_code, array $lang_args = null, $data = null, int $http_code = null,
	                             int $encoding_options = null): HttpResponse
	{
		return static::buildErrorResponse($data, $api_code, $http_code, $lang_args, $encoding_options);
	}

	/**
	 * @param integer           $api_code         API code to be returned with the response
	 * @param object|array|null $data             payload to be returned as 'data' node, @null if none
	 * @param array|null        $lang_args        arguments array passed to Lang::get() for messages with placeholders
	 * @param integer|null      $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use
	 *                                            config's value or defaults
	 *
	 * @return HttpResponse
	 */
	public static function errorWithData(int $api_code, $data, array $lang_args = null,
	                                     int $encoding_options = null): HttpResponse
	{
		return static::buildErrorResponse($data, $api_code, null, $lang_args, $encoding_options);
	}

	/**
	 * @param integer           $api_code         API code to be returned with the response
	 * @param object|array|null $data             payload to be returned as 'data' node, @null if none
	 * @param integer|null      $http_code        HTTP error code to be returned with this Cannot be @null
	 * @param array|null        $lang_args        arguments array passed to Lang::get() for messages with placeholders
	 * @param integer|null      $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use
	 *                                            config's value or defaults
	 *
	 * @return HttpResponse
	 *
	 * @throws \InvalidArgumentException if http_code is @null
	 */
	public static function errorWithDataAndHttpCode(int $api_code, $data, int $http_code, array $lang_args = null,
	                                                int $encoding_options = null): HttpResponse
	{
		return static::buildErrorResponse($data, $api_code, $http_code, $lang_args, $encoding_options);
	}

	/**
	 * @param integer      $api_code  API code to be returned with the response
	 * @param integer|null $http_code HTTP return code to be set for this response or @null for default
	 * @param array|null   $lang_args arguments array passed to Lang::get() for messages with placeholders
	 *
	 * @return HttpResponse
	 *
	 * @throws \InvalidArgumentException if http_code is @null
	 */
	public static function errorWithHttpCode(int $api_code, int $http_code, array $lang_args = null): HttpResponse
	{
		return static::buildErrorResponse(null, $api_code, $http_code, $lang_args);
	}

	/**
	 * @param integer           $api_code         API code to be returned with the response
	 * @param string            $error_message    custom message to be returned as part of error response
	 * @param object|array|null $data             payload to be returned as 'data' node, @null if none
	 * @param integer|null      $http_code        optional HTTP status code to be used with this response or @null for defaults
	 * @param integer|null      $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use config's
	 *                                            value or defaults
	 *
	 * @return HttpResponse
	 */
	public static function errorWithMessageAndData(int $api_code, string $error_message, $data,
	                                               int $http_code = null, int $encoding_options = null): HttpResponse
	{
		return static::buildErrorResponse($data, $api_code, $http_code, null,
			$error_message, null, $encoding_options);
	}

	/**
	 * @param integer           $api_code         API code to be returned with the response
	 * @param string            $error_message    custom message to be returned as part of error response
	 * @param object|array|null $data             payload to be returned as 'data' node, @null if none
	 * @param integer|null      $http_code        optional HTTP status code to be used with this response or @null for defaults
	 * @param integer|null      $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use
	 *                                            config's value or defaults
	 * @param array|null        $debug_data       optional debug data array to be added to returned JSON.
	 *
	 * @return HttpResponse
	 */
	public static function errorWithMessageAndDataAndDebug(int $api_code, string $error_message, $data,
	                                                       int $http_code = null, int $encoding_options = null,
	                                                       array $debug_data = null): HttpResponse
	{
		return static::buildErrorResponse($data, $api_code, $http_code, null,
			$error_message, null, $encoding_options, $debug_data);
	}

	/**
	 * @param integer      $api_code      API code to be returned with the response
	 * @param string       $error_message custom message to be returned as part of error response
	 * @param integer|null $http_code     optional HTTP status code to be used with this response or @null for defaults
	 *
	 * @return HttpResponse
	 */
	public static function errorWithMessage(int $api_code, string $error_message, int $http_code = null): HttpResponse
	{
		return static::buildErrorResponse(null, $api_code, $http_code, null, $error_message);
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error message
	 * uses placeholders as well as return data payload
	 *
	 * @param object|array|null $data             payload array to be returned in 'data' node or response object or @null if none
	 * @param integer           $api_code         API code to be returned with the response
	 * @param integer|null      $http_code        optional HTTP status code to be used with this response or @null for default
	 * @param array|null        $lang_args        arguments array passed to Lang::get() for messages with placeholders
	 * @param string|null       $message          custom message to be returned as part of error response
	 * @param array|null        $headers          optional HTTP headers to be returned in error response
	 * @param integer|null      $encoding_options see see json_encode() docs for valid option values. Use @null to fall back to
	 *                                            config's value or defaults
	 * @param array|null        $debug_data       optional debug data array to be added to returned JSON.
	 *
	 * @return HttpResponse
	 *
	 * @throws \InvalidArgumentException Thrown if $code is not correct, outside the range, equals OK code etc.
	 *
	 * @noinspection MoreThanThreeArgumentsInspection
	 */
	protected static function buildErrorResponse($data, int $api_code, int $http_code = null, array $lang_args = null,
	                                             string $message = null, array $headers = null, int $encoding_options = null,
	                                             array $debug_data = null): HttpResponse
	{
		$http_code = $http_code ?? static::DEFAULT_HTTP_CODE_ERROR;
		$headers = $headers ?? [];

		$code_ok = BaseApiCodes::OK();

		Validator::assertInt('api_code', $api_code);
		if ($api_code !== $code_ok) {
			Validator::assertIntRange('api_code', $api_code, BaseApiCodes::getMinCode(), BaseApiCodes::getMaxCode());
		}
		if ($api_code === $code_ok) {
			throw new \InvalidArgumentException("Error response cannot use api_code of value  {$code_ok} which is reserved for OK");
		}

		Validator::assertInt('http_code', $http_code);
		Validator::assertIntRange('http_code', $http_code, static::ERROR_HTTP_CODE_MIN, static::ERROR_HTTP_CODE_MAX);

		$message_or_api_code = $message ?? $api_code;

		return static::make(false, $api_code, $message_or_api_code, $data, $http_code,
			$lang_args, $headers, $encoding_options, $debug_data);
	}

	/**
	 * @param boolean           $success             @true if response indicate success, @false otherwise
	 * @param integer           $api_code            API code to be returned with the response
	 * @param string|integer    $message_or_api_code message string or valid API code
	 * @param object|array|null $data                optional additional data to be included in response object
	 * @param integer|null      $http_code           return HTTP code for build Response object
	 * @param array|null        $lang_args           arguments array passed to Lang::get() for messages with placeholders
	 * @param array|null        $headers             optional HTTP headers to be returned in the response
	 * @param integer|null      $encoding_options    see http://php.net/manual/en/function.json-encode.php
	 * @param array|null        $debug_data          optional debug data array to be added to returned JSON.
	 *
	 * @return HttpResponse
	 *
	 * @throws \InvalidArgumentException If $api_code is neither a string nor valid integer code.
	 * @throws \InvalidArgumentException if $data is an object of class that is not configured in "classes" mapping.
	 *
	 * @noinspection MoreThanThreeArgumentsInspection
	 */
	protected static function make(bool $success, int $api_code, $message_or_api_code, $data = null,
	                               int $http_code = null, array $lang_args = null, array $headers = null,
	                               int $encoding_options = null, array $debug_data = null): HttpResponse
	{
		$headers = $headers ?? [];
		$http_code = $http_code ?? ($success ? static::DEFAULT_HTTP_CODE_OK : static::DEFAULT_HTTP_CODE_ERROR);
		$encoding_options = $encoding_options ?? Config::get(self::CONF_KEY_ENCODING_OPTIONS, static::DEFAULT_ENCODING_OPTIONS);

		Validator::assertInt('encoding_options', $encoding_options);

		Validator::assertInt('api_code', $api_code);
		if (!BaseApiCodes::isCodeValid($api_code)) {
			$min = BaseApiCodes::getMinCode();
			$max = BaseApiCodes::getMaxCode();
			throw new \InvalidArgumentException("API code value ({$api_code}) is out of allowed range {$min}-{$max}");
		}

		if (!(is_int($message_or_api_code) || is_string($message_or_api_code))) {
			throw new \InvalidArgumentException(
				sprintf('Message must be either string or resolvable integer API code (%s given)',
					gettype($message_or_api_code))
			);
		}

		// we got code, not message string, so we need to check if we have the mapping for
		// this string already configured.
		if (is_int($message_or_api_code)) {
			$key = BaseApiCodes::getCodeMessageKey($message_or_api_code);
			if ($key === null) {
				// nope, let's get the default one instead
				$key = BaseApiCodes::getCodeMessageKey($success ? BaseApiCodes::OK() : BaseApiCodes::NO_ERROR_MESSAGE());
			}

			$lang_args = $lang_args ?? ['api_code' => $message_or_api_code];
			$message_or_api_code = \Lang::get($key, $lang_args);
		}

		return Response::json(
			static::buildResponse($success, $api_code, $message_or_api_code, $data, $debug_data),
			$http_code, $headers, $encoding_options
		);
	}

	/**
	 * Creates standardised API response array. If you set APP_DEBUG to true, 'code_hex' field will be
	 * additionally added to reported JSON for easier manual debugging.
	 *
	 * @param boolean           $success    @true if response indicates success, @false otherwise
	 * @param integer           $api_code   response code
	 * @param string            $message    message to return
	 * @param object|array|null $data       API response data if any
	 * @param array|null        $debug_data optional debug data array to be added to returned JSON.
	 *
	 * @return array response ready to be encoded as json and sent back to client
	 *
	 * @throws \RuntimeException in case of missing or invalid "classes" mapping configuration
	 */
	protected static function buildResponse(bool $success, int $api_code, string $message, $data = null,
	                                        array $debug_data = null): array
	{
		// ensure $data is either @null, array or object of class with configured mapping.
		if ($data !== null) {
			if (!is_array($data) && !is_object($data)) {
				throw new \InvalidArgumentException(
					sprintf('Invalid payload data. Must be null, array or class with mapping ("%s" given).', gettype($data)));
			}

			if (is_object($data) && !static::hasClassesMapping($data)) {
				throw new \InvalidArgumentException(sprintf('No mapping configured for "%s" class.', get_class($data)));
			}

			// Preliminary validation passed. Let's walk and convert...
			// we can do some auto-conversion on known class types, so check for that first
			/** @var array $classes */
			$classes = static::getClassesMapping();
			if (($classes !== null) && (count($classes) > 0)) {
				if (is_array($data)) {
					static::convert($classes, $data);
				} elseif (is_object($data)) {
					$obj_class_name = get_class($data);
					if (array_key_exists($obj_class_name, $classes)) {
						$conversion_method = $classes[ $obj_class_name ][ static::KEY_METHOD ];
						$data = [$classes[ $obj_class_name ][ static::KEY_KEY ] => $data->$conversion_method()];
					}
				}
			}
		}

		if ($data !== null && !is_object($data)) {
			// ensure we get object in final JSON structure in data node
			$data = (object)$data;
		}

		/** @noinspection PhpUndefinedClassInspection */
		$response = [
			static::KEY_SUCCESS => $success,
			static::KEY_CODE    => $api_code,
			static::KEY_LOCALE  => \App::getLocale(),
			static::KEY_MESSAGE => $message,
			static::KEY_DATA    => $data,
		];

		if ($debug_data !== null) {
			$debug_key = Config::get(static::CONF_KEY_DEBUG_DEBUG_KEY, self::KEY_DEBUG);
			$response[ $debug_key ] = $debug_data;
		}

		return $response;
	}


}
