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
	 * Creates standardised API response array. If you set APP_DEBUG to true, 'code_hex' field will be
	 * additionally added to reported JSON for easier manual debugging.
	 *
	 * @param integer $code    response code (not http response code)
	 * @param string  $message error message or 'OK'
	 * @param mixed   $data    api response data if any
	 *
	 * @return array response array ready to be encoded as json and sent back to client
	 */
	protected static function buildResponse($code, $message, $data = null)
	{
		// ensure data is serialized as object, not plain array, regardless what we are provided as argument
		if ($data !== null) {
			if ($data instanceof Illuminate\Database\Eloquent\Model) {
				$key = 'classes.' . Illuminate\Database\Eloquent\Model::class . '.key';
				$data = [Config.get($key, 'item') => $data->toArray()];
			} elseif ($data instanceof Illuminate\Database\Eloquent\Collection) {
				$key = 'classes.' . Illuminate\Database\Eloquent\Collection::class . '.key';
				$data = [Config.get($key, 'items') => $data->toArray()];
			}

			// ensure we get object in final JSON structure in data node
			$data = (object)$data;
		}

		$response = ['success' => ($code === ErrorCode::OK),
		             'code'    => $code,
		             'locale'  => \App::getLocale(),
		             'message' => $message,
		             'data'    => $data,
		];

		return $response;
	}

	/**
	 * Returns success
	 *
	 * @param mixed|null $data      payload to be returned as 'data' node, @null if none
	 * @param integer    $http_code HTTP return code to be set for this response (HttpResponse::HTTP_OK (200) is default)
	 * @param array|null $lang_args array of arguments passed to Lang if message associated with error_code uses placeholders
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function success($data = null, $http_code = HttpResponse::HTTP_OK, array $lang_args = null)
	{
		return static::buildSuccessResponse($data, ErrorCode::OK, $http_code, $lang_args);
	}

	/**
	 * Returns success with custom HTTP code
	 *
	 * @param integer $http_code HTTP return code to be set for this response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function successWithHttpCode($http_code)
	{
		return static::buildSuccessResponse(null, ErrorCode::OK, $http_code, []);
	}

	/**
	 * @param mixed|null   $data        payload to be returned as 'data' node, @null if none
	 * @param integer      $return_code numeric code to be returned as 'code' @\App\ErrorCode::OK is default
	 * @param integer|null $http_code   HTTP return code to be set for this response (is default HttpResponse::HTTP_OK)
	 * @param array|null   $lang_args   array of arguments passed to Lang if message associated with error_code uses placeholders
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException Thrown when provided arguments are invalid.
	 *
	 */
	protected static function buildSuccessResponse($data = null, $return_code = ErrorCode::OK, $http_code = null, array $lang_args = null)
	{
		if ($http_code === null) {
			$http_code = HttpResponse::HTTP_OK;
		}

		if (!is_int($return_code)) {
			throw new \InvalidArgumentException('error_code must be integer');
		}
		if (!is_int($http_code)) {
			throw new \InvalidArgumentException('http_code must be integer');
		} else if (($http_code < 199) || ($http_code > 299)) {
			throw new \InvalidArgumentException('http_code must be in range 200-299 inclusive.');
		}

		return static::make($return_code, $return_code, $data, $http_code, $lang_args);
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error
	 * message uses placeholders as well as return data payload
	 *
	 * @param integer      $error_code internal error code with matching error message
	 * @param array|null   $lang_args  if array, then this passed as arguments to Lang::get() to build final string.
	 * @param mixed|null   $data       payload array to be returned in 'data' node or response object
	 * @param integer|null $http_code  optional HTTP status code to be used with this response. Default HttpResponse::HTTP_BAD_REQUEST
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function error($error_code, array $lang_args = null, $data = null, $http_code = null)
	{
		return static::buildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	/**
	 * @param integer    $error_code numeric code to be returned as 'code'
	 * @param mixed|null $data       payload to be returned as 'data' node, @null if none
	 * @param array|null $lang_args  |null optional array with arguments passed to Lang::get()
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithData($error_code, $data, array $lang_args = null)
	{
		return static::buildErrorResponse($data, $error_code, HttpResponse::HTTP_BAD_REQUEST, $lang_args);
	}

	/**
	 * @param integer    $error_code numeric code to be returned as 'code'
	 * @param mixed|null $data       payload to be returned as 'data' node, @null if none
	 * @param integer    $http_code  HTTP error code to be returned with this Cannot be @null
	 * @param array|null $lang_args  |null optional array with arguments passed to Lang::get()
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \RuntimeException if http_code is @null
	 */
	public static function errorWithDataAndHttpCode($error_code, $data, $http_code, array $lang_args = null)
	{
		if ($http_code === null) {
			throw new \RuntimeException('http_code cannot be null. Use errorWithData() instead.');
		}

		return static::buildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	/**
	 * @param integer    $error_code numeric code to be returned as 'code'
	 * @param integer    $http_code  HTTP return code to be set for this response or @null for default
	 * @param array|null $lang_args  |null optional array with arguments passed to Lang::get()
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \RuntimeException if http_code is @null
	 */
	public static function errorWithHttpCode($error_code, $http_code, array $lang_args = null)
	{
		if ($http_code === null) {
			throw new \RuntimeException('http_code cannot be null. Use error() instead.');
		}

		return static::buildErrorResponse(null, $error_code, $http_code, $lang_args);
	}

	/**
	 * @param integer      $error_code    numeric code to be returned as 'code'
	 * @param string       $error_message custom message to be returned as part of error response
	 * @param mixed|null   $data          payload to be returned as 'data' node, @null if none
	 * @param integer|null $http_code     optional HTTP status code to be used with this response or @null for defaults
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithMessageAndData($error_code, $error_message, $data, $http_code = HttpResponse::HTTP_BAD_REQUEST)
	{
		return static::buildErrorResponse($data, $error_code, $http_code, null, $error_message);
	}

	/**
	 * @param integer      $error_code    numeric code to be returned as 'code'
	 * @param string       $error_message custom message to be returned as part of error response
	 * @param integer|null $http_code optional HTTP status code to be used with this response or @null for defaults
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithMessage($error_code, $error_message, $http_code = null)
	{
		return static::buildErrorResponse(null, $error_code, $http_code, [], $error_message);
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error message
	 * uses placeholders as well as return data payload
	 *
	 * @param mixed|null   $data       payload array to be returned in 'data' node or response object
	 * @param integer      $error_code internal error code with matching error message
	 * @param integer|null $http_code  optional HTTP status code to be used with this response or @null for default HttpResponse::HTTP_BAD_REQUEST
	 * @param array|null   $lang_args  if array, then this passed as arguments to Lang::get() to build final string.
	 * @param string|null  $message    custom message to be returned as part of error response
	 * @param array|null   $headers    optional HTTP headers to be returned in error response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException Thrown if $code is not correct, outside the range, equals OK code etc.
	 */
	protected static function buildErrorResponse($data, $error_code, $http_code, array $lang_args = null, $message = null, array $headers = null)
	{
		if ($http_code === null) {
			$http_code = HttpResponse::HTTP_BAD_REQUEST;
		}

		if (!is_int($error_code)) {
			throw new \InvalidArgumentException('error_code must be integer');
		} elseif ($error_code == ErrorCode::OK) {
			throw new \InvalidArgumentException('error_code must not be equal to ErrorCode::OK');
		} elseif ((!is_array($lang_args)) && ($lang_args !== null)) {
			throw new \InvalidArgumentException('lang_args must be either array or null');
		} elseif (!is_int($http_code)) {
			throw new \InvalidArgumentException('http_code must be integer');
		} elseif ($http_code < 400) {
			throw new \InvalidArgumentException('http_code cannot be lower than 400');
		}

		if ($message === null) {
			$message = $error_code;
		}
		if ($headers === null) {
			$headers = [];
		}

		return static::make($error_code, $message, $data, $http_code, $lang_args, $headers);
	}


	/**
	 * @param integer        $return_code     internal message code (usually 0 for OK, and unique integer for errors)
	 * @param string|integer $message_or_code error message string or error code (must be mapped correctly too)
	 * @param mixed|null     $data            optional additional data to be included in response object
	 * @param integer        $http_code       return HTTP code for build Response object
	 * @param array          $lang_args       |null optional array with arguments passed to Lang::get()
	 * @param array          $headers         |null optional HTTP headers to be returned in error response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @throws \InvalidArgumentException If code is neither a string nor integer.
	 */
	protected static function make($return_code, $message_or_code, $data, $http_code, array $lang_args = null, array $headers = null)
	{
		if ($lang_args === null) {
			$lang_args = [];
		}
		if ($headers === null) {
			$headers = [];
		}
		if ($headers === null) {
			$headers = [];
		}

		// are we given message test already?
		if (!is_string($message_or_code)) {
			// no, so it must be an int value
			if (!is_int($message_or_code)) {
				throw new \InvalidArgumentException('Message must be either string or resolvable error code');
			}

			// do we have the mapping for this string already?
			$key = ErrorCode::getMapping($message_or_code);
			if ($key === null) {
				// no, get the default one instead
				$key = ErrorCode::getMapping(ErrorCode::NO_ERROR_MESSAGE);
				$lang_args = ['error_code' => $message_or_code];
			}
			$message_or_code = \Lang::get($key, $lang_args);
		} else {
			if (!is_int($message_or_code)) {
				throw new \InvalidArgumentException('Error code must be integer value');
			}

			if (!ErrorCode::isCodeValid($message_or_code)) {
				throw new \InvalidArgumentException("Error code {$message_or_code} is out of allowed range");
			}
		}

		return Response::json(static::buildResponse($return_code, $message_or_code, $data), $http_code, $headers);
	}
}
