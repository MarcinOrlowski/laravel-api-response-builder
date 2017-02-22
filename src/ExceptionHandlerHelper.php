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
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

/**
 * Class ExceptionHandlerHelper
 */
class ExceptionHandlerHelper
{
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
		if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
			switch ($exception->getStatusCode()) {
				case HttpResponse::HTTP_NOT_FOUND:
					$result = static::error($exception, 'http_not_found', ApiCodeBase::EX_HTTP_NOT_FOUND);
					break;

				case HttpResponse::HTTP_SERVICE_UNAVAILABLE:
					$result = static::error($exception, 'http_service_unavailable', ApiCodeBase::EX_HTTP_SERVICE_UNAVAILABLE);
					break;

				default:
					$result = static::error($exception, 'http_exception', ApiCodeBase::EX_HTTP_EXCEPTION,
						HttpResponse::HTTP_BAD_REQUEST);
					break;
			}
		} else {
			$result = static::error($exception, 'uncaught_exception', HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
		}

		return $result;
	}

	/**
	 * @param Exception $exception
	 * @param string    $config_base
	 * @param integer   $default_api_code
	 * @param integer   $default_http_code
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected static function error(Exception $exception, $config_base,
	                                $default_api_code, $default_http_code = HttpResponse::HTTP_BAD_REQUEST)
	{
		$api_code = Config::get("response_builder.exception_handler.exception.{$config_base}.code", $default_api_code);
		$http_code = Config::get("response_builder.exception_handler.exception.{$config_base}.http_code", 0);

		// check if this is valid HTTP error code
		if ($http_code === 0) {
			// no code, let's try getting the exception status
			if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
				$http_code = $exception->getStatusCode();
			} else {
				$http_code = $exception->getCode();
			}

			// can it be considered valid HTTP error code?
			if ($http_code < 400) {
				$http_code = 0;
			}
		} elseif ($http_code < 400) {
			$http_code = 0;
		}

		// still no code? use default
		if ($http_code === 0) {
			$http_code = $default_http_code;
		}

		$data = [];
		if (Config::get('app.debug')) {
			$data = [
				'class' => get_class($exception),
				'file'  => $exception->getFile(),
				'line'  => $exception->getLine(),
			];
		}

		// let's figure out what event we are handling now
		$base_config = 'response_builder.exception_handler.exception';
		if (Config::get("{$base_config}.http_not_found.code", ApiCodeBase::EX_HTTP_NOT_FOUND) === $api_code) {
			$base_api_code = ApiCodeBase::EX_HTTP_NOT_FOUND;
		} elseif (Config::get("{$base_config}.http_service_unavailable.code", ApiCodeBase::EX_HTTP_SERVICE_UNAVAILABLE) === $api_code) {
			$base_api_code = ApiCodeBase::EX_HTTP_SERVICE_UNAVAILABLE;
		} elseif (Config::get("{$base_config}.http_exception.code", ApiCodeBase::EX_HTTP_EXCEPTION) === $api_code) {
			$base_api_code = ApiCodeBase::EX_HTTP_EXCEPTION;
		} elseif (Config::get("{$base_config}.uncaught_exception.code", ApiCodeBase::EX_UNCAUGHT_EXCEPTION) === $api_code) {
			$base_api_code = ApiCodeBase::EX_UNCAUGHT_EXCEPTION;
		} else {
			$base_api_code = ApiCodeBase::NO_ERROR_MESSAGE;
		}

		$key = ApiCodeBase::getMapping($api_code);
		if ($key === null) {
			$key = ApiCodeBase::getBaseMapping($base_api_code);
		}

		// let's build error message
		$error_message = '';
		$ex_message = trim($exception->getMessage());
		if (Config::get('response_builder.exception_handler.use_exception_message_first', true)) {
			if ($ex_message === '') {
				$ex_message = get_class($exception);
			} else {
				$error_message = $ex_message;
			}
		}

		if ($error_message === '') {
			$error_message = Lang::get($key, [
				'error_code' => $api_code,      // LEGACY!
				'api_code' => $api_code,
				'message'    => $ex_message,
				'class'      => get_class($exception),
			]);
		}

		return ResponseBuilder::errorWithMessageAndData($api_code, $error_message, $data, $http_code);
	}

}
