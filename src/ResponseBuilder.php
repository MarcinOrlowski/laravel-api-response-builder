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
	const DEFAULT_API_CODE_OK = ApiCodeBase::OK;

	/**
	 * Reads and validates "classes" config mapping
	 *
	 * @return array|null Classes mapping as specified in configuration or @null if no such config found
	 *
	 * @throws \RuntimeException if "classes" mapping is invalid
	 */
	protected static function getClassesMapping()
	{
		$classes = Config::get('response_builder.classes');

		if ($classes !== null) {
			if (!is_array($classes)) {
				throw new \RuntimeException(sprintf('CONFIG: "classes" mapping must be an array (\'%s\' given)', gettype($classes)));
			}

			$mandatory_keys = ['key',
			                   'method',
			];
			foreach ($classes as $class_name => $class_config) {
				foreach ($mandatory_keys as $key_name) {
					if (!array_key_exists($key_name, $class_config)) {
						throw new \RuntimeException('CONFIG: Missing "{$key_name}" for "{$class_name}" mapping');
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
					$conversion_method = $classes[ $obj_class_name ]['method'];
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
	 * @param boolean $success  @true if reposnse indicates success, @false otherwise
	 * @param integer $api_code response code
	 * @param string  $message  message to return
	 * @param mixed   $data     API response data if any
	 *
	 * @return array response ready to be encoded as json and sent back to client
	 *
	 * @throws \RuntimeException in case of missing or invalid "classes" mapping configuration
	 */
	protected static function buildResponse($success, $api_code, $message, $data = null)
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
						$conversion_method = $classes[$obj_class_name]['method'];
						$data = [$classes[$obj_class_name]['key'] => $data->$conversion_method()];
					}
				}
			}

			// ensure we get object in final JSON structure in data node
			$data = (object)$data;
		}

		/** @noinspection UnnecessaryParenthesesInspection */
		$response = ['success' => $success,
		             'code'    => $api_code,
		             'locale'  => \App::getLocale(),
		             'message' => $message,
		             'data'    => $data,
		];

		return $response;
	}

	/**
	 * Returns success
	 *
	 * @param mixed|null   $data      payload to be returned as 'data' node, @null if none
	 * @param int|null     $api_code  API code to be returned with the response or @null for default value
	 * @param array|null   $lang_args array of arguments passed to Lang if message associated with api_code uses placeholders
	 * @param integer|null $http_code HTTP return code to be set for this response or @null for default (200)
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function success($data = null, $api_code = null, array $lang_args = null, $http_code = null)
	{
		if ($api_code === null) {
			$api_code = static::DEFAULT_API_CODE_OK;
		}

		return static::buildSuccessResponse($data, $api_code, $lang_args, $http_code);
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
	 * @param mixed|null   $data      payload to be returned as 'data' node, @null if none
	 * @param integer|null $api_code  numeric code to be returned as 'code' or null for default value
	 * @param array|null   $lang_args array of arguments passed to Lang if message associated with api_code uses placeholders
	 * @param integer|null $http_code HTTP return code to be set for this response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException Thrown when provided arguments are invalid.
	 *
	 */
	protected static function buildSuccessResponse($data = null, $api_code = null, array $lang_args = null, $http_code = null)
	{
		if ($http_code === null) {
			$http_code = static::DEFAULT_HTTP_CODE_OK;
		}
		if ($api_code === null) {
			$api_code = static::DEFAULT_API_CODE_OK;
		}

		if (!is_int($api_code)) {
			throw new \InvalidArgumentException(sprintf("api_code must be integer ('%s' given)", gettype($api_code)));
		}
		if (!is_int($http_code)) {
			throw new \InvalidArgumentException(sprintf("http_code must be integer ('%s' given)", gettype($http_code)));
		} elseif (($http_code < 200) || ($http_code > 299)) {
			throw new \InvalidArgumentException(sprintf('http_code value is invalid. Must be in range 200-299 inclusive, %d given', $http_code));
		}

		return static::make(true, $api_code, $api_code, $data, $http_code, $lang_args);
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error
	 * message uses placeholders as well as return data payload
	 *
	 * @param integer      $api_code  internal API code to be returned
	 * @param array|null   $lang_args if array, then this passed as arguments to Lang::get() to build final string.
	 * @param mixed|null   $data      payload array to be returned in 'data' node or response object
	 * @param integer|null $http_code optional HTTP status code to be used with this response or @null for default
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function error($api_code, array $lang_args = null, $data = null, $http_code = null)
	{
		return static::buildErrorResponse($data, $api_code, $http_code, $lang_args);
	}

	/**
	 * @param integer    $api_code  numeric code to be returned as 'code'
	 * @param mixed|null $data      payload to be returned as 'data' node, @null if none
	 * @param array|null $lang_args |null optional array with arguments passed to Lang::get()
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithData($api_code, $data, array $lang_args = null)
	{
		return static::buildErrorResponse($data, $api_code, null, $lang_args);
	}

	/**
	 * @param integer    $api_code  numeric code to be returned as 'code'
	 * @param mixed|null $data      payload to be returned as 'data' node, @null if none
	 * @param integer    $http_code HTTP error code to be returned with this Cannot be @null
	 * @param array|null $lang_args |null optional array with arguments passed to Lang::get()
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException if http_code is @null
	 */
	public static function errorWithDataAndHttpCode($api_code, $data, $http_code, array $lang_args = null)
	{
		if ($http_code === null) {
			throw new \InvalidArgumentException('http_code cannot be null. Use errorWithData() instead');
		}

		return static::buildErrorResponse($data, $api_code, $http_code, $lang_args);
	}

	/**
	 * @param integer    $api_code  numeric code to be returned as 'code'
	 * @param integer    $http_code HTTP return code to be set for this response or @null for default
	 * @param array|null $lang_args |null optional array with arguments passed to Lang::get()
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
	 * @param integer      $api_code      numeric code to be returned as 'code'
	 * @param string       $error_message custom message to be returned as part of error response
	 * @param mixed|null   $data          payload to be returned as 'data' node, @null if none
	 * @param integer|null $http_code     optional HTTP status code to be used with this response or @null for defaults
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithMessageAndData($api_code, $error_message, $data, $http_code = null)
	{
		return static::buildErrorResponse($data, $api_code, $http_code, null, $error_message);
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
	 * @param mixed|null   $data      payload array to be returned in 'data' node or response object
	 * @param integer      $api_code  internal API code value to be returned
	 * @param integer|null $http_code optional HTTP status code to be used with this response or @null for default
	 * @param array|null   $lang_args if array, then this passed as arguments to Lang::get() to build final string.
	 * @param string|null  $message   custom message to be returned as part of error response
	 * @param array|null   $headers   optional HTTP headers to be returned in error response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException Thrown if $code is not correct, outside the range, equals OK code etc.
	 *
	 * @noinspection MoreThanThreeArgumentsInspection
	 */
	protected static function buildErrorResponse($data, $api_code, $http_code, $lang_args = null, $message = null, $headers = null)
	{
		if ($http_code === null) {
			$http_code = static::DEFAULT_HTTP_CODE_ERROR;
		}

		if (!is_int($api_code)) {
			throw new \InvalidArgumentException(sprintf("api_code must be integer ('%s' given)", gettype($api_code)));
		} elseif ($api_code === static::DEFAULT_API_CODE_OK) {
			throw new \InvalidArgumentException(sprintf('api_code must not be equal to DEFAULT_API_CODE_OK (%d)', static::DEFAULT_API_CODE_OK));
		} elseif ((!is_array($lang_args)) && ($lang_args !== null)) {
			throw new \InvalidArgumentException(sprintf("lang_args must be either array or null ('%s' given)", gettype($lang_args)));
		} elseif (!is_int($http_code)) {
			throw new \InvalidArgumentException(sprintf("http_code must be integer ('%s' given)", gettype($http_code)));
		} elseif ($http_code < 400) {
			throw new \InvalidArgumentException('http_code cannot be lower than 400');
		}

		if ($message === null) {
			$message = $api_code;
		}
		if ($headers === null) {
			$headers = [];
		}

		return static::make(false, $api_code, $message, $data, $http_code, $lang_args, $headers);
	}


	/**
	 * @param boolean        $success             @true if reponse indicate success, @false otherwise
	 * @param integer        $api_code            internal code you want to return with the message
	 * @param string|integer $message_or_api_code message string or API code
	 * @param mixed|null     $data                optional additional data to be included in response object
	 * @param integer|null   $http_code           return HTTP code for build Response object
	 * @param array          $lang_args           |null optional array with arguments passed to Lang::get()
	 * @param array          $headers             |null optional HTTP headers to be returned in the response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException If code is neither a string nor integer.
	 *
	 * @noinspection MoreThanThreeArgumentsInspection
	 */
	protected static function make($success, $api_code, $message_or_api_code, $data = null,
	                               $http_code = null, array $lang_args = null, array $headers = null)
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

		// are we given message text already?
		if (!is_string($message_or_api_code)) {
			// no, so it must be an int value
			if (!is_int($message_or_api_code)) {
				throw new \InvalidArgumentException(
					sprintf('Message must be either string or resolvable integer api_code (\'%s\' given)', gettype($message_or_api_code))
				);
			}

			// do we have the mapping for this string already?
			$key = ApiCodeBase::getMapping($message_or_api_code);
			if ($key === null) {
				// no, get the default one instead
				$key = ApiCodeBase::getMapping($success
						? ApiCodeBase::OK
						: ApiCodeBase::NO_ERROR_MESSAGE
				);
			}
			$message_or_api_code = \Lang::get($key, $lang_args);
		} else {
			if (!is_int($api_code)) {
				throw new \InvalidArgumentException(
					sprintf("api_code must be integer ('%s' given)", gettype($api_code))
				);
			}

			if (!ApiCodeBase::isCodeValid($api_code)) {
				$msg = sprintf("API code value ({$api_code}) is out of allowed range %d-%d",
					ApiCodeBase::getMinCode(), ApiCodeBase::getMaxCode());
				throw new \InvalidArgumentException($msg);
			}
		}

		return Response::json(static::buildResponse($success, $api_code, $message_or_api_code, $data), $http_code, $headers);
	}
}
