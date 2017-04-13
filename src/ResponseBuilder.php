<?php

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

namespace MarcinOrlowski\ResponseBuilder;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Builds standardized \Symfony\Component\HttpFoundation\Response response object
 */
class ResponseBuilder
{
	/**
	 * Default HTTP code to be used with success responses
	 */
	const DEFAULT_HTTP_CODE_OK    = HttpResponse::HTTP_OK;

	/**
	 * Default HTTP code to be used with error responses
	 */
	const DEFAULT_HTTP_CODE_ERROR = HttpResponse::HTTP_BAD_REQUEST;

	/**
	 * Default API code for OK
	 */
	const DEFAULT_API_CODE_OK = BaseApiCodes::OK;

	/**
	 * Configuration keys
	 */
	const CONF_KEY_DEBUG_DEBUG_KEY        = 'response_builder.debug.debug_key';
	const CONF_KEY_DEBUG_EX_TRACE_ENABLED = 'response_builder.debug.exception_handler.trace_enabled';
	const CONF_KEY_DEBUG_EX_TRACE_KEY     = 'response_builder.debug.exception_handler.trace_key';
	const CONF_KEY_MAP                    = 'response_builder.map';
	const CONF_KEY_ENCODING_OPTIONS       = 'response_builder.encoding_options';
	const CONF_KEY_CLASSES                = 'response_builder.classes';
	const CONF_KEY_MIN_CODE               = 'response_builder.min_code';
	const CONF_KEY_MAX_CODE               = 'response_builder.max_code';
	const CONF_KEY_RESPONSE_KEY_MAP       = 'response_builder.response_key_map';

	/**
	 * Default keys to be used by exception handler while adding debug information
	 */
	const KEY_DEBUG   = 'debug';
	const KEY_TRACE   = 'trace';
	const KEY_CLASS   = 'class';
	const KEY_FILE    = 'file';
	const KEY_LINE    = 'line';
	const KEY_KEY     = 'key';
	const KEY_METHOD  = 'method';
	const KEY_SUCCESS = 'success';
	const KEY_CODE    = 'code';
	const KEY_LOCALE  = 'locale';
	const KEY_MESSAGE = 'message';
	const KEY_DATA    = 'data';

	/**
	 * Default key to be used by exception handler while processing ValidationException
	 * to return all the error messages
	 */
	const KEY_MESSAGES = 'messages';

	/**
	 * Default JSON encoding options
	 *
	 * 271 = JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE
	 *
	 * This must be as int due to const limits in PHP disallowing expressions.
	 */
	const DEFAULT_ENCODING_OPTIONS = 271;

	/**
	 * Reads and validates "classes" config mapping
	 *
	 * @return array|null Classes mapping as specified in configuration or @null if no such config found
	 *
	 * @throws \RuntimeException if "classes" mapping is invalid
	 */
	protected static function getClassesMapping()
	{
		$classes = Config::get(ResponseBuilder::CONF_KEY_CLASSES);

		if ($classes !== null) {
			if (!is_array($classes)) {
				throw new \RuntimeException(sprintf('CONFIG: "classes" mapping must be an array (%s given)', gettype($classes)));
			}

			$mandatory_keys = [static::KEY_KEY,
			                   static::KEY_METHOD,
			];
			foreach ($classes as $class_name => $class_config) {
				foreach ($mandatory_keys as $key_name) {
					if (!array_key_exists($key_name, $class_config)) {
						throw new \RuntimeException(sprintf("CONFIG: Missing '%s' for '%s' class mapping", $key_name, $class_name));
					}
				}
			}
		}

		return $classes;
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
	protected static function convert(array $classes, array &$data)
	{
		foreach ($data as $data_key => &$data_val) {
			if (is_array($data_val)) {
				static::convert($classes, $data_val);
			} elseif (is_object($data_val)) {
				$obj_class_name = get_class($data_val);
				if (array_key_exists($obj_class_name, $classes)) {
					$conversion_method = $classes[ $obj_class_name ][static::KEY_METHOD];
					$converted = $data_val->$conversion_method();
					$data[ $data_key ] = $converted;
				}
			}
		}
	}


	/**
	 * Creates standardised API response array. If you set APP_DEBUG to true, 'code_hex' field will be
	 * additionally added to reported JSON for easier manual debugging.
	 *
	 * @param boolean    $success    @true if reposnse indicates success, @false otherwise
	 * @param integer    $api_code   response code
	 * @param string     $message    message to return
	 * @param mixed      $data       API response data if any
	 * @param array|null $trace_data optional debug data array to be added to returned JSON.
	 *
	 * @return array response ready to be encoded as json and sent back to client
	 *
	 * @throws \RuntimeException in case of missing or invalid "classes" mapping configuration
	 */
	protected static function buildResponse($success, $api_code, $message, $data = null, array $trace_data = null)
	{
		// ensure data is serialized as object, not plain array, regardless what we are provided as argument
		if ($data !== null) {
			// we can do some auto-conversion on known class types, so check for that first
			/** @var array $classes */
			$classes = static::getClassesMapping();
			if (($classes !== null) && (count($classes) > 0)) {
				if (is_array($data)) {
					static::convert($classes, $data);
				} elseif (is_object($data)) {
					$obj_class_name = get_class($data);
					if (array_key_exists($obj_class_name, $classes)) {
						$conversion_method = $classes[$obj_class_name][static::KEY_METHOD];
						$data = [$classes[$obj_class_name][static::KEY_KEY] => $data->$conversion_method()];
					}
				}
			}
		}

		$response = [
			BaseApiCodes::getResponseKey(static::KEY_SUCCESS) => $success,
			BaseApiCodes::getResponseKey(static::KEY_CODE)    => $api_code,
			BaseApiCodes::getResponseKey(static::KEY_LOCALE)  => \App::getLocale(),
			BaseApiCodes::getResponseKey(static::KEY_MESSAGE) => $message,
			BaseApiCodes::getResponseKey(static::KEY_DATA)    => $data,
		];

		if ($trace_data !== null) {
			$debug_key = Config::get(static::CONF_KEY_DEBUG_DEBUG_KEY, ResponseBuilder::KEY_DEBUG);
			$response[$debug_key] = $trace_data;
		}

		if ($data !== null) {
			// ensure we get object in final JSON structure in data node
			$data = (object)$data;
		}

		return $response;
	}

	/**
	 * Returns success
	 *
	 * @param mixed|null   $data             payload to be returned as 'data' node, @null if none
	 * @param integer|null $api_code         API code to be returned with the response or @null for default value
	 * @param array|null   $lang_args        array of arguments passed to Lang if message associated with api_code uses placeholders
	 * @param integer|null $http_code        HTTP return code to be set for this response or @null for default (200)
	 * @param integer|null $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use config's value or defaults
	 *
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function success($data = null, $api_code = null, array $lang_args = null, $http_code = null, $encoding_options = null)
	{
		if ($api_code === null) {
			$api_code = static::DEFAULT_API_CODE_OK;
		}

		return static::buildSuccessResponse($data, $api_code, $lang_args, $http_code, $encoding_options);
	}

	/**
	 * Returns success
	 *
	 * @param integer|null $api_code  API code to be returned with the response or @null for default value
	 * @param array|null   $lang_args array of arguments passed to Lang if message associated with api_code uses placeholders
	 * @param integer|null $http_code HTTP return code to be set for this response or @null for default (200)
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function successWithCode($api_code, array $lang_args = null, $http_code = null)
	{
		return static::success(null, $api_code, $lang_args, $http_code);
	}

	/**
	 * Returns success with custom HTTP code
	 *
	 * @param integer $http_code HTTP return code to be set for this response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException when http_code is @null
	 */
	public static function successWithHttpCode($http_code)
	{
		if ($http_code === null) {
			throw new \InvalidArgumentException('http_code cannot be null. Use success() instead');
		}

		return static::buildSuccessResponse(null, static::DEFAULT_API_CODE_OK, [], $http_code);
	}

	/**
	 * @param mixed|null   $data             payload to be returned as 'data' node, @null if none
	 * @param integer|null $api_code         numeric code to be returned as 'code' or null for default value
	 * @param array|null   $lang_args        array of arguments passed to Lang if message associated with api_code uses placeholders
	 * @param integer|null $http_code        HTTP return code to be set for this response
	 * @param integer|null $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use config's value or defaults
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException Thrown when provided arguments are invalid.
	 *
	 */
	protected static function buildSuccessResponse($data = null, $api_code = null, array $lang_args = null, $http_code = null, $encoding_options = null)
	{
		if ($http_code === null) {
			$http_code = static::DEFAULT_HTTP_CODE_OK;
		}
		if ($api_code === null) {
			$api_code = static::DEFAULT_API_CODE_OK;
		}

		if (!is_int($api_code)) {
			throw new \InvalidArgumentException(sprintf('api_code must be integer (%s given)', gettype($api_code)));
		}
		if (!is_int($http_code)) {
			throw new \InvalidArgumentException(sprintf('http_code must be integer (%s given)', gettype($http_code)));
		}
		if (($http_code < 200) || ($http_code > 299)) {
			throw new \InvalidArgumentException(sprintf('Invalid http_code (%d). Must be between 200-299 inclusive', $http_code));
		}

		return static::make(true, $api_code, $api_code, $data, $http_code, $lang_args, null, $encoding_options);
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error
	 * message uses placeholders as well as return data payload
	 *
	 * @param integer      $api_code         internal API code to be returned
	 * @param array|null   $lang_args        if array, then this passed as arguments to Lang::get() to build final string.
	 * @param mixed|null   $data             payload array to be returned in 'data' node or response object
	 * @param integer|null $http_code        optional HTTP status code to be used with this response or @null for default
	 * @param integer|null $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use config's value or defaults
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function error($api_code, array $lang_args = null, $data = null, $http_code = null, $encoding_options = null)
	{
		return static::buildErrorResponse($data, $api_code, $http_code, $lang_args, $encoding_options);
	}

	/**
	 * @param integer      $api_code         numeric code to be returned as 'code'
	 * @param mixed|null   $data             payload to be returned as 'data' node, @null if none
	 * @param array|null   $lang_args        optional array with arguments passed to Lang::get()
	 * @param integer|null $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use config's value or defaults
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithData($api_code, $data, array $lang_args = null, $encoding_options = null)
	{
		return static::buildErrorResponse($data, $api_code, null, $lang_args, $encoding_options);
	}

	/**
	 * @param integer      $api_code         numeric code to be returned as 'code'
	 * @param mixed|null   $data             payload to be returned as 'data' node, @null if none
	 * @param integer      $http_code        HTTP error code to be returned with this Cannot be @null
	 * @param array|null   $lang_args        optional array with arguments passed to Lang::get()
	 * @param integer|null $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use config's value or defaults
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException if http_code is @null
	 */
	public static function errorWithDataAndHttpCode($api_code, $data, $http_code, array $lang_args = null, $encoding_options = null)
	{
		if ($http_code === null) {
			throw new \InvalidArgumentException('http_code cannot be null. Use errorWithData() instead');
		}

		return static::buildErrorResponse($data, $api_code, $http_code, $lang_args, $encoding_options);
	}

	/**
	 * @param integer    $api_code  numeric code to be returned as 'code'
	 * @param integer    $http_code HTTP return code to be set for this response or @null for default
	 * @param array|null $lang_args optional array with arguments passed to Lang::get()
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException if http_code is @null
	 */
	public static function errorWithHttpCode($api_code, $http_code, array $lang_args = null)
	{
		if ($http_code === null) {
			throw new \InvalidArgumentException('http_code cannot be null. Use error() instead');
		}

		return static::buildErrorResponse(null, $api_code, $http_code, $lang_args);
	}

	/**
	 * @param integer      $api_code         numeric code to be returned as 'code'
	 * @param string       $error_message    custom message to be returned as part of error response
	 * @param mixed|null   $data             payload to be returned as 'data' node, @null if none
	 * @param integer|null $http_code        optional HTTP status code to be used with this response or @null for defaults
	 * @param integer|null $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use config's value or defaults
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithMessageAndData($api_code, $error_message, $data, $http_code = null, $encoding_options = null)
	{
		return static::buildErrorResponse($data, $api_code, $http_code, null, $error_message, $encoding_options);
	}

	/**
	 * @param integer      $api_code         numeric code to be returned as 'code'
	 * @param string       $error_message    custom message to be returned as part of error response
	 * @param mixed|null   $data             payload to be returned as 'data' node, @null if none
	 * @param integer|null $http_code        optional HTTP status code to be used with this response or @null for defaults
	 * @param integer|null $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use config's value or defaults
	 * @param array|null   $debug_data       optional debug data array to be added to returned JSON.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithMessageAndDataAndDebug($api_code, $error_message, $data, $http_code = null,
	                                                       $encoding_options = null, $debug_data = null)
	{
		return static::buildErrorResponse($data, $api_code, $http_code, null, $error_message, null, $encoding_options, $debug_data);
	}

	/**
	 * @param integer      $api_code      numeric code to be returned as 'code'
	 * @param string       $error_message custom message to be returned as part of error response
	 * @param integer|null $http_code     optional HTTP status code to be used with this response or @null for defaults
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithMessage($api_code, $error_message, $http_code = null)
	{
		return static::buildErrorResponse(null, $api_code, $http_code, [], $error_message);
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error message
	 * uses placeholders as well as return data payload
	 *
	 * @param mixed|null   $data             payload array to be returned in 'data' node or response object
	 * @param integer      $api_code         internal API code value to be returned
	 * @param integer|null $http_code        optional HTTP status code to be used with this response or @null for default
	 * @param array|null   $lang_args        if array, then this passed as arguments to Lang::get() to build final string.
	 * @param string|null  $message          custom message to be returned as part of error response
	 * @param array|null   $headers          optional HTTP headers to be returned in error response
	 * @param integer|null $encoding_options see http://php.net/manual/en/function.json-encode.php or @null to use config's value or defaults
	 * @param array|null   $debug_data       optional debug data array to be added to returned JSON.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException Thrown if $code is not correct, outside the range, equals OK code etc.
	 *
	 * @noinspection MoreThanThreeArgumentsInspection
	 */
	protected static function buildErrorResponse($data, $api_code, $http_code, $lang_args = null, $message = null,
	                                             $headers = null, $encoding_options = null, $debug_data = null)
	{
		if ($http_code === null) {
			$http_code = static::DEFAULT_HTTP_CODE_ERROR;
		}

		if (!is_int($api_code)) {
			throw new \InvalidArgumentException(sprintf('api_code must be integer (%s given)', gettype($api_code)));
		}
		if ($api_code === static::DEFAULT_API_CODE_OK) {
			throw new \InvalidArgumentException(sprintf('api_code must not be %d (DEFAULT_API_CODE_OK)', static::DEFAULT_API_CODE_OK));
		}
		if ((!is_array($lang_args)) && ($lang_args !== null)) {
			throw new \InvalidArgumentException(sprintf('lang_args must be either array or null (%s given)', gettype($lang_args)));
		}
		if (!is_int($http_code)) {
			throw new \InvalidArgumentException(sprintf('http_code must be integer (%s given)', gettype($http_code)));
		}
		if (($http_code < 400) || ($http_code > 499)) {
			throw new \InvalidArgumentException('http_code must be in range from 400 to 499 inclusive');
		}

		if ($message === null) {
			$message = $api_code;
		}
		if ($headers === null) {
			$headers = [];
		}

		return static::make(false, $api_code, $message, $data, $http_code, $lang_args, $headers, $encoding_options, $debug_data);
	}


	/**
	 * @param boolean        $success             @true if reponse indicate success, @false otherwise
	 * @param integer        $api_code            internal code you want to return with the message
	 * @param string|integer $message_or_api_code message string or API code
	 * @param mixed|null     $data                optional additional data to be included in response object
	 * @param integer|null   $http_code           return HTTP code for build Response object
	 * @param array|null     $lang_args           optional array with arguments passed to Lang::get()
	 * @param array|null     $headers             optional HTTP headers to be returned in the response
	 * @param integer|null   $encoding_options    see http://php.net/manual/en/function.json-encode.php
	 * @param array|null     $debug_data          optional debug data array to be added to returned JSON.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException If code is neither a string nor integer.
	 *
	 * @noinspection MoreThanThreeArgumentsInspection
	 */
	protected static function make($success, $api_code, $message_or_api_code, $data = null,
	                               $http_code = null, array $lang_args = null, array $headers = null,
	                               $encoding_options = null, array $debug_data = null
	)
	{
		if ($lang_args === null) {
			$lang_args = ['api_code' => $message_or_api_code];
		}
		if ($headers === null) {
			$headers = [];
		}
		if ($http_code === null) {
			$http_code = $success
				? static::DEFAULT_HTTP_CODE_OK
				: static::DEFAULT_HTTP_CODE_ERROR;
		}

		if ($encoding_options === null) {
			$encoding_options = Config::get(ResponseBuilder::CONF_KEY_ENCODING_OPTIONS, static::DEFAULT_ENCODING_OPTIONS);
		}
		if (!is_int($encoding_options)) {
			throw new \InvalidArgumentException(sprintf('encoding_options must be integer (%s given)', gettype($encoding_options)));
		}

		// are we given message text already?
		if (!is_string($message_or_api_code)) {
			// no, so it must be an int value
			if (!is_int($message_or_api_code)) {
				throw new \InvalidArgumentException(
					sprintf('Message must be either string or resolvable integer api_code (%s given)', gettype($message_or_api_code))
				);
			}

			// do we have the mapping for this string already?
			$key = BaseApiCodes::getCodeMessageKey($message_or_api_code);
			if ($key === null) {
				// no, get the default one instead
				$key = BaseApiCodes::getCodeMessageKey($success
						? BaseApiCodes::OK
						: BaseApiCodes::NO_ERROR_MESSAGE
				);
			}
			$message_or_api_code = \Lang::get($key, $lang_args);
		} else {
			if (!is_int($api_code)) {
				throw new \InvalidArgumentException(
					sprintf('api_code must be integer (%s given)', gettype($api_code))
				);
			}

			if (!BaseApiCodes::isCodeValid($api_code)) {
				$msg = sprintf('API code value (%d) is out of allowed range %d-%d',
					$api_code, BaseApiCodes::getMinCode(), BaseApiCodes::getMaxCode());
				throw new \InvalidArgumentException($msg);
			}
		}

		return Response::json(
			static::buildResponse($success, $api_code, $message_or_api_code, $data, $debug_data),
			$http_code, $headers, $encoding_options
		);
	}
}
