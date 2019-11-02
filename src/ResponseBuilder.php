<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
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
	 *
	 * @var int
	 */
	public const DEFAULT_HTTP_CODE_OK = HttpResponse::HTTP_OK;

	/**
	 * Default HTTP code to be used with error responses
	 *
	 * @var int
	 */
	public const DEFAULT_HTTP_CODE_ERROR = HttpResponse::HTTP_BAD_REQUEST;

	/**
	 * Min allowed HTTP code for errorXXX()
	 *
	 * @var int
	 */
	public const ERROR_HTTP_CODE_MIN = 400;

	/**
	 * Max allowed HTTP code for errorXXX()
	 *
	 * @var int
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
	public const CONF_EXCEPTION_HANDLER_KEY      = 'response_builder.exception_handler';

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
	 *
	 * @var string
	 */
	public const KEY_MESSAGES = 'messages';

	/**
	 * Default JSON encoding options. Must be specified as final value (i.e. 271) and NOT
	 * PHP expression i.e. `JSON_HEX_TAG|JSON_HEX_APOS|...` as such syntax is not yet supported
	 * by PHP.
	 *
	 * 271 = JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE
	 *
	 * @var int
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
	 * Returns success
	 *
	 * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
	 *                                         of the JSON response, single supported object or @null if there's no
	 *                                         to be returned.
	 * @param integer|null      $api_code      API code to be returned or @null to use value of BaseApiCodes::OK().
	 * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
	 *                                         substitution or @null if none.
	 * @param integer|null      $http_code     HTTP code to be used for HttpResponse sent or @null
	 *                                         for default DEFAULT_HTTP_CODE_OK.
	 * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
	 *                                         options or pass @null to use value from your config (or defaults).
	 *
	 * @return HttpResponse
	 */
	public static function success($data = null, $api_code = null, array $placeholders = null,
	                               int $http_code = null, int $json_opts = null): HttpResponse
	{
		return static::buildSuccessResponse($data, $api_code, $placeholders, $http_code, $json_opts);
	}

	/**
	 * Returns success
	 *
	 * @param integer|null $api_code      API code to be returned or @null to use value of BaseApiCodes::OK().
	 * @param array|null   $placeholders  Placeholders passed to Lang::get() for message placeholders
	 *                                    substitution or @null if none.
	 * @param integer|null $http_code     HTTP code to be used for HttpResponse sent or @null
	 *                                    for default DEFAULT_HTTP_CODE_OK.
	 *
	 * @return HttpResponse
	 */
	public static function successWithCode(int $api_code = null, array $placeholders = null,
	                                       int $http_code = null): HttpResponse
	{
		return static::success(null, $api_code, $placeholders, $http_code);
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
	 * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node.
	 *                                         of the JSON response, single supported object or @null if there's no
	 *                                         to be returned.
	 * @param integer|null      $api_code      API code to be returned or @null to use value of BaseApiCodes::OK().
	 * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
	 *                                         substitution or @null if none.
	 * @param integer|null      $http_code     HTTP code to be used for HttpResponse sent or @null
	 *                                         for default DEFAULT_HTTP_CODE_OK.
	 * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
	 *                                         options or pass @null to use value from your config (or defaults).
	 *
	 * @return HttpResponse
	 *
	 * @throws \InvalidArgumentException Thrown when provided arguments are invalid.
	 */
	protected static function buildSuccessResponse($data = null, int $api_code = null, array $placeholders = null,
	                                               int $http_code = null, int $json_opts = null): HttpResponse
	{
		$http_code = $http_code ?? static::DEFAULT_HTTP_CODE_OK;
		$api_code = $api_code ?? BaseApiCodes::OK();

		Validator::assertInt('api_code', $api_code);
		Validator::assertInt('http_code', $http_code);
		Validator::assertIntRange('http_code', $http_code, 200, 299);

		return static::make(true, $api_code, $api_code, $data, $http_code, $placeholders, null, $json_opts);
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error
	 * message uses placeholders as well as return data payload
	 *
	 * @param integer           $api_code      Your API code to be returned with the response object.
	 * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
	 *                                         substitution or @null if none.
	 * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
	 *                                         of the JSON response, single supported object or @null if there's no
	 *                                         to be returned.
	 * @param integer|null      $http_code     HTTP code to be used for HttpResponse sent or @null
	 *                                         for default DEFAULT_HTTP_CODE_ERROR.
	 * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
	 *                                         options or pass @null to use value from your config (or defaults).
	 *
	 * @return HttpResponse
	 */
	public static function error(int $api_code, array $placeholders = null, $data = null, int $http_code = null,
	                             int $encoding_options = null): HttpResponse
	{
		return static::buildErrorResponse($data, $api_code, $http_code, $placeholders, $encoding_options);
	}

	/**
	 * @param integer           $api_code      Your API code to be returned with the response object.
	 * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
	 *                                         of the JSON response, single supported object or @null if there's no
	 *                                         to be returned.
	 * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
	 *                                         substitution or @null if none.
	 * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
	 *                                         options or pass @null to use value from your config (or defaults).
	 *
	 * @return HttpResponse
	 */
	public static function errorWithData(int $api_code, $data, array $placeholders = null,
	                                     int $json_opts = null): HttpResponse
	{
		return static::buildErrorResponse($data, $api_code, null, $placeholders, $json_opts);
	}

	/**
	 * @param integer           $api_code      Your API code to be returned with the response object.
	 * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
	 *                                         of the JSON response, single supported object or @null if there's no
	 *                                         to be returned.
	 * @param integer           $http_code     HTTP code to be used for HttpResponse sent.
	 * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
	 *                                         substitution or @null if none.
	 * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
	 *                                         options or pass @null to use value from your config (or defaults).
	 *
	 * @return HttpResponse
	 *
	 * @throws \InvalidArgumentException if http_code is @null
	 */
	public static function errorWithDataAndHttpCode(int $api_code, $data, int $http_code, array $placeholders = null,
	                                                int $json_opts = null): HttpResponse
	{
		return static::buildErrorResponse($data, $api_code, $http_code, $placeholders, $json_opts);
	}

	/**
	 * @param integer    $api_code     Your API code to be returned with the response object.
	 * @param integer    $http_code    HTTP code to be used for HttpResponse sent or @null
	 *                                 for default DEFAULT_HTTP_CODE_ERROR.
	 * @param array|null $placeholders Placeholders passed to Lang::get() for message placeholders
	 *                                 substitution or @null if none.
	 *
	 * @return HttpResponse
	 *
	 * @throws \InvalidArgumentException if http_code is @null
	 */
	public static function errorWithHttpCode(int $api_code, int $http_code, array $placeholders = null): HttpResponse
	{
		return static::buildErrorResponse(null, $api_code, $http_code, $placeholders);
	}

	/**
	 * @param integer           $api_code  Your API code to be returned with the response object.
	 * @param string            $message   Custom message to be returned as part of error response
	 * @param object|array|null $data      Array of primitives and supported objects to be returned in 'data' node
	 *                                     of the JSON response, single supported object or @null if there's no
	 *                                     to be returned.
	 * @param integer|null      $http_code Optional HTTP status code to be used for HttpResponse sent
	 *                                     or @null for DEFAULT_HTTP_CODE_ERROR
	 * @param integer|null      $json_opts See http://php.net/manual/en/function.json-encode.php for supported
	 *                                     options or pass @null to use value from your config (or defaults).
	 *
	 * @return HttpResponse
	 */
	public static function errorWithMessageAndData(int $api_code, string $message, $data,
	                                               int $http_code = null, int $json_opts = null): HttpResponse
	{
		return static::buildErrorResponse($data, $api_code, $http_code, null,
			$message, null, $json_opts);
	}

	/**
	 * @param integer           $api_code   Your API code to be returned with the response object.
	 * @param string            $message    custom message to be returned as part of error response
	 * @param object|array|null $data       Array of primitives and supported objects to be returned in 'data' node
	 *                                      of the JSON response, single supported object or @null if there's no
	 *                                      to be returned.
	 * @param integer|null      $http_code  HTTP code to be used for HttpResponse sent or @null
	 *                                      for default DEFAULT_HTTP_CODE_ERROR.
	 * @param integer|null      $json_opts  See http://php.net/manual/en/function.json-encode.php for supported
	 *                                      options or pass @null to use value from your config (or defaults).
	 * @param array|null        $debug_data optional debug data array to be added to returned JSON.
	 *
	 * @return HttpResponse
	 */
	public static function errorWithMessageAndDataAndDebug(int $api_code, string $message, $data,
	                                                       int $http_code = null, int $json_opts = null,
	                                                       array $debug_data = null): HttpResponse
	{
		return static::buildErrorResponse($data, $api_code, $http_code, null,
			$message, null, $json_opts, $debug_data);
	}

	/**
	 * @param integer      $api_code  Your API code to be returned with the response object.
	 * @param string       $message   Custom message to be returned as part of error response
	 * @param integer|null $http_code HTTP code to be used with final response sent or @null
	 *                                for default DEFAULT_HTTP_CODE_ERROR.
	 *
	 * @return HttpResponse
	 */
	public static function errorWithMessage(int $api_code, string $message, int $http_code = null): HttpResponse
	{
		return static::buildErrorResponse(null, $api_code, $http_code, null, $message);
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error message
	 * uses placeholders as well as return data payload
	 *
	 * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
	 *                                         of the JSON response, single supported object or @null if there's no
	 *                                         to be returned.
	 * @param integer           $api_code      Your API code to be returned with the response object.
	 * @param integer|null      $http_code     HTTP code to be used for HttpResponse sent or @null
	 *                                         for default DEFAULT_HTTP_CODE_ERROR.
	 * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
	 *                                         substitution or @null if none.
	 * @param string|null       $message       custom message to be returned as part of error response
	 * @param array|null        $headers       optional HTTP headers to be returned in error response
	 * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
	 *                                         options or pass @null to use value from your config (or defaults).
	 * @param array|null        $debug_data    optional debug data array to be added to returned JSON.
	 *
	 * @return HttpResponse
	 *
	 * @throws \InvalidArgumentException Thrown if $code is not correct, outside the range, equals OK code etc.
	 *
	 * @noinspection MoreThanThreeArgumentsInspection
	 */
	protected static function buildErrorResponse($data, int $api_code, int $http_code = null,
	                                             array $placeholders = null,
	                                             string $message = null, array $headers = null,
	                                             int $json_opts = null,
	                                             array $debug_data = null): HttpResponse
	{
		$http_code = $http_code ?? static::DEFAULT_HTTP_CODE_ERROR;
		$headers = $headers ?? [];

		Validator::assertInt('api_code', $api_code);

		$code_ok = BaseApiCodes::OK();
		if ($api_code !== $code_ok) {
			Validator::assertIntRange('api_code', $api_code, BaseApiCodes::getMinCode(), BaseApiCodes::getMaxCode());
		}
		if ($api_code === $code_ok) {
			throw new \InvalidArgumentException(
				"Error response cannot use api_code of value  {$code_ok} which is reserved for OK");
		}

		Validator::assertInt('http_code', $http_code);
		Validator::assertIntRange('http_code', $http_code, static::ERROR_HTTP_CODE_MIN, static::ERROR_HTTP_CODE_MAX);

		$msg_or_api_code = $message ?? $api_code;

		return static::make(false, $api_code, $msg_or_api_code, $data, $http_code,
			$placeholders, $headers, $json_opts, $debug_data);
	}

	/**
	 * @param boolean           $success         @true if response reports successful operation, @false otherwise.
	 * @param integer           $api_code        Your API code to be returned with the response object.
	 * @param string|integer    $msg_or_api_code message string or valid API code to get message for
	 * @param object|array|null $data            optional additional data to be included in response object
	 * @param integer|null      $http_code       HTTP code for the HttpResponse or @null for either DEFAULT_HTTP_CODE_OK
	 *                                           or DEFAULT_HTTP_CODE_ERROR depending on the $success.
	 * @param array|null        $placeholders    Placeholders passed to Lang::get() for message placeholders
	 *                                           substitution or @null if none.
	 * @param array|null        $headers         Optional HTTP headers to be returned in the response.
	 * @param integer|null      $json_opts       See http://php.net/manual/en/function.json-encode.php for supported
	 *                                           options or pass @null to use value from your config (or defaults).
	 * @param array|null        $debug_data      Optional debug data array to be added to returned JSON.
	 *
	 * @return HttpResponse
	 *
	 * @throws \InvalidArgumentException If $api_code is neither a string nor valid integer code.
	 * @throws \InvalidArgumentException if $data is an object of class that is not configured in "classes" mapping.
	 *
	 * @noinspection MoreThanThreeArgumentsInspection
	 */
	protected static function make(bool $success, int $api_code, $msg_or_api_code, $data = null,
	                               int $http_code = null, array $placeholders = null, array $headers = null,
	                               int $json_opts = null, array $debug_data = null): HttpResponse
	{
		$headers = $headers ?? [];
		$http_code = $http_code ?? ($success ? static::DEFAULT_HTTP_CODE_OK : static::DEFAULT_HTTP_CODE_ERROR);
		$json_opts = $json_opts ?? Config::get(self::CONF_KEY_ENCODING_OPTIONS, static::DEFAULT_ENCODING_OPTIONS);

		Validator::assertInt('encoding_options', $json_opts);

		Validator::assertInt('api_code', $api_code);
		if (!BaseApiCodes::isCodeValid($api_code)) {
			$min = BaseApiCodes::getMinCode();
			$max = BaseApiCodes::getMaxCode();
			throw new \InvalidArgumentException("API code value ({$api_code}) is out of allowed range {$min}-{$max}");
		}

		return Response::json(
			static::buildResponse($success, $api_code, $msg_or_api_code, $placeholders, $data, $debug_data),
			$http_code, $headers, $json_opts);
	}

	/**
	 * If $msg_or_api_code is integer value, returns human readable message associated with that code (with
	 * fallback to built-in default string if no api code mapping is set. If $msg_or_api_code is a string,
	 * returns it unaltered.
	 *
	 * @param boolean    $success      @true if response reports successful operation, @false otherwise.
	 * @param integer    $api_code     Your API code to be returned with the response object.
	 * @param array|null $placeholders Placeholders passed to Lang::get() for message placeholders
	 *                                 substitution or @null if none.
	 *
	 * @return string
	 */
	protected static function getMessageForApiCode(bool $success, int $api_code, array $placeholders = null): string
	{
		// We got integer value here not a message string, so we need to check if we have the mapping for
		// this string already configured.
		$key = BaseApiCodes::getCodeMessageKey($api_code);
		if ($key === null) {
			// nope, let's get the default one instead, based of
			$fallback_code = $success ? BaseApiCodes::OK() : BaseApiCodes::NO_ERROR_MESSAGE();
			$key = BaseApiCodes::getCodeMessageKey($fallback_code);
		}

		$placeholders = $placeholders ?? [];
		if (!array_key_exists('api_code', $placeholders)) {
			$placeholders['api_code'] = $api_code;
		}

		return \Lang::get($key, $placeholders);
	}

	/**
	 * Creates standardised API response array. This is final method called in the whole pipeline before we
	 * return final JSON back to client. If you want to manipulate your response, this is the place to do that.
	 * If you set APP_DEBUG to true, 'code_hex' field will be additionally added to reported JSON for easier
	 * manual debugging.
	 *
	 * @param boolean           $success         @true if response reports successful operation, @false otherwise.
	 * @param integer           $api_code        Your API code to be returned with the response object.
	 * @param string|integer    $msg_or_api_code Message string or valid API code to get message for.
	 * @param array|null        $placeholders    Placeholders passed to Lang::get() for message placeholders
	 *                                           substitution or @null if none.
	 * @param object|array|null $data            API response data if any
	 * @param array|null        $debug_data      optional debug data array to be added to returned JSON.
	 *
	 * @return array response ready to be encoded as json and sent back to client
	 *
	 * @throws \RuntimeException in case of missing or invalid "classes" mapping configuration
	 */
	protected static function buildResponse(bool $success, int $api_code,
	                                        $msg_or_api_code, array $placeholders = null,
	                                        $data = null, array $debug_data = null): array
	{
		// ensure $data is either @null, array or object of class with configured mapping.
		$converter = new Converter();

		$data = $converter->convert($data);
		if ($data !== null && !is_object($data)) {
			// ensure we get object in final JSON structure in data node
			$data = (object)$data;
		}

		// get human readable message for API code or use message string (if given instead of API code)
		if (is_int($msg_or_api_code)) {
			$message = self::getMessageForApiCode($success, $msg_or_api_code, $placeholders);
		} else {
			Validator::assertString('message', $msg_or_api_code);
			$message = $msg_or_api_code;
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
