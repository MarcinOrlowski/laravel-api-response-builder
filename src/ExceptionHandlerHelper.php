<?php

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Exception handler using ResponseBuilder to return JSON even in such hard tines
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;


/**
 * Class ExceptionHandlerHelper
 */
class ExceptionHandlerHelper
{
	/**
	 * Exception types
	 */
	const TYPE_HTTP_NOT_FOUND           = 'http_not_found';
	const TYPE_HTTP_SERVICE_UNAVAILABLE = 'http_service_unavailable';
	const TYPE_HTTP_UNAUTHORIZED        = 'authentication_exception';
	const TYPE_DEFAULT                  = 'http_exception';
	const TYPE_VALIDATION_EXCEPTION     = 'validation_exception';
	const TYPE_UNCAUGHT_EXCEPTION       = 'uncaught_exception';

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request $request   Request object
	 * @param  \Exception               $exception Exception
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function render($request, Exception $exception)
	{
		if ($exception instanceof HttpException) {
			switch ($exception->getStatusCode()) {
				case HttpResponse::HTTP_NOT_FOUND:
					$result = static::error($exception, static::TYPE_HTTP_NOT_FOUND, BaseApiCodes::EX_HTTP_NOT_FOUND);
					break;

				case HttpResponse::HTTP_SERVICE_UNAVAILABLE:
					$result = static::error($exception, static::TYPE_HTTP_SERVICE_UNAVAILABLE, BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE);
					break;

				case HttpResponse::HTTP_UNAUTHORIZED:
					$result = static::error($exception, static::TYPE_HTTP_UNAUTHORIZED, BaseApiCodes::EX_AUTHENTICATION_EXCEPTION);
					break;

				default:
					$result = static::error($exception, static::TYPE_DEFAULT, BaseApiCodes::EX_HTTP_EXCEPTION);
					break;
			}
		} elseif ($exception instanceof ValidationException) {
			$result = static::error($exception, static::TYPE_VALIDATION_EXCEPTION, BaseApiCodes::EX_VALIDATION_EXCEPTION);
		} else {
			$result = static::error($exception, static::TYPE_UNCAUGHT_EXCEPTION, BaseApiCodes::EX_UNCAUGHT_EXCEPTION);
		}

		return $result;
	}


	/**
	 * Convert an authentication exception into an unauthenticated response.
	 *
	 * @param  \Illuminate\Http\Request                 $request
	 * @param  \Illuminate\Auth\AuthenticationException $exception
	 *
	 * @return \Illuminate\Http\Response
	 */
	protected function unauthenticated($request, AuthenticationException $exception)
	{
		return static::error($exception, 'authentication_exception', BaseApiCodes::EX_AUTHENTICATION_EXCEPTION);
	}


	/**
	 * @param Exception $exception         Exception to be processed
	 * @param string    $exception_type    Category of the exception
	 * @param integer   $default_api_code  API code to return
	 * @param integer   $default_http_code HTTP code to return
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected static function error(Exception $exception, $exception_type,
	                                $default_api_code, $default_http_code = ResponseBuilder::DEFAULT_HTTP_CODE_ERROR)
	{
		$base_config = 'response_builder.exception_handler.exception';

		$api_code = Config::get("{$base_config}.{$exception_type}.code", $default_api_code);
		$http_code = Config::get("{$base_config}.{$exception_type}.http_code", 0);

		// check if this is valid HTTP error code
		if ($http_code === 0) {
			// no code, let's try getting the exception status
			if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
				$http_code = $exception->getStatusCode();
			} else {
				$http_code = $exception->getCode();
			}

			// can it be considered valid HTTP error code?
			if (($http_code < 400) || ($http_code > 499)) {
				$http_code = 0;
			}
		} elseif (($http_code < 400) || ($http_code > 499)) {
			$http_code = 0;
		}

		// still no code? use default
		if ($http_code === 0) {
			$http_code = $default_http_code;
		}

		$trace_data = null;
		if (Config::get(ResponseBuilder::CONF_KEY_DEBUG_EX_TRACE_ENABLED, false)) {
			$trace_data = [
				Config::get(ResponseBuilder::CONF_KEY_DEBUG_EX_TRACE_KEY, ResponseBuilder::KEY_TRACE) => [
					ResponseBuilder::KEY_CLASS => get_class($exception),
					ResponseBuilder::KEY_FILE  => $exception->getFile(),
					ResponseBuilder::KEY_LINE  => $exception->getLine(),
				],
			];
		}

		// optional payload to return
		$data = null;

		// let's figure out what event we are handling now
		if (Config::get("{$base_config}.http_not_found.code", BaseApiCodes::EX_HTTP_NOT_FOUND) === $api_code) {
			$base_api_code = BaseApiCodes::EX_HTTP_NOT_FOUND;
		} elseif (Config::get("{$base_config}.http_service_unavailable.code", BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE) === $api_code) {
			$base_api_code = BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE;
		} elseif (Config::get("{$base_config}.http_exception.code", BaseApiCodes::EX_HTTP_EXCEPTION) === $api_code) {
			$base_api_code = BaseApiCodes::EX_HTTP_EXCEPTION;
		} elseif (Config::get("{$base_config}.uncaught_exception.code", BaseApiCodes::EX_UNCAUGHT_EXCEPTION) === $api_code) {
			$base_api_code = BaseApiCodes::EX_UNCAUGHT_EXCEPTION;
		} elseif (Config::get("{$base_config}.authentication_exception.code", BaseApiCodes::EX_AUTHENTICATION_EXCEPTION) === $api_code) {
			$base_api_code = BaseApiCodes::EX_AUTHENTICATION_EXCEPTION;
		} elseif (Config::get("{$base_config}.validation_exception.code", BaseApiCodes::EX_VALIDATION_EXCEPTION) === $api_code) {
			$base_api_code = BaseApiCodes::EX_VALIDATION_EXCEPTION;
			$data = [ResponseBuilder::KEY_MESSAGES => $exception->validator->errors()->messages()];
		} else {
			$base_api_code = BaseApiCodes::NO_ERROR_MESSAGE;
		}

		$key = BaseApiCodes::getCodeMessageKey($api_code);
		if ($key === null) {
			$key = BaseApiCodes::getReservedCodeMessageKey($base_api_code);
		}

		// let's build error message
		$error_message = '';
		$ex_message = trim($exception->getMessage());

		// ensure we won't fail due to exception incorect encoding
		if (!mb_check_encoding($ex_message, 'UTF-8')) {
			// let's check there's iconv and mb_string available
			if (function_exists('iconv') && function_exists('mb_detec_encoding')) {
				$ex_message = iconv(mb_detect_encoding($ex_message, mb_detect_order(), true), 'UTF-8', $ex_message);
			} else {
				// lame fallback, in case there's no iconv/mb_string installed
				$ex_message = htmlspecialchars_decode(htmlspecialchars($ex_message, ENT_SUBSTITUTE, 'UTF-8'));
			}
		}

		if (Config::get('response_builder.exception_handler.use_exception_message_first', true)) {
			if ($ex_message === '') {
				$ex_message = get_class($exception);
			} else {
				$error_message = $ex_message;
			}
		}

		if ($error_message === '') {
			$error_message = Lang::get($key, [
				'api_code' => $api_code,
				'message'  => $ex_message,
				'class'    => get_class($exception),
			]);
		}

		return ResponseBuilder::errorWithMessageAndDataAndDebug($api_code, $error_message, $data, $http_code, null, $trace_data);
	}

}
