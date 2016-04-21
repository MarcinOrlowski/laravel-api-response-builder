<?php

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Exception handler using ResponseBuilder to return JSON even in such hard tines
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016 Marcin Orlowski
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
	 * @param  \Illuminate\Http\Request $request Request object
	 * @param  \Exception               $ex      Exception
	 *
	 * @return \Illuminate\Http\Response
	 */
	public static function render($request, Exception $ex)
	{
		if ($ex instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
			switch ($ex->getStatusCode()) {
				case HttpResponse::HTTP_NOT_FOUND:
					$result = static::error($ex, 'http_not_found', ErrorCode::EX_HTTP_NOT_FOUND);
					break;

				case HttpResponse::HTTP_SERVICE_UNAVAILABLE:
					$result = static::error($ex, 'http_service_unavailable', ErrorCode::EX_HTTP_SERVICE_UNAVAILABLE);
					break;

				default:
					$msg = trim($ex->getMessage());
					if ($msg == '') {
						$msg = 'Exception code #' . $ex->getStatusCode();
					}

					$result = static::error($ex, 'http_exception', ErrorCode::EX_HTTP_EXCEPTION,
						HttpResponse::HTTP_BAD_REQUEST, ['message' => $msg]);
					break;
			}
		} else {
			$result = static::error($ex, 'uncaught_exception', HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
		}

		return $result;
	}

	/**
	 * @param Exception $ex
	 * @param string    $config_base
	 * @param integer   $default_error_code
	 * @param integer   $default_http_code
	 *
	 * @return Response
	 */
	protected static function error(Exception $ex, $config_base, $default_error_code, $default_http_code = HttpResponse::HTTP_BAD_REQUEST)
	{
		$error_code = Config::get("response_builder.exception_handler.exception.{$config_base}.code", $default_error_code);
		$http_code = Config::get("response_builder.exception_handler.exception.{$config_base}.http_code", 0);

		// check if this is valid HTTP error code
		if ($http_code < 400) {
			$http_code = 0;
		} elseif ($http_code == 0) {
			// no code, let's try exception status
			$http_code = $ex->getStatusCode();

			// can it be valid HTTP error code?
			if ($http_code < 400) {
				$http_code = 0;
			}
		}
		// still no code? use default
		if ($http_code == 0) {
			$http_code = $default_http_code;
		}

		$data = [];
		if (Config::get('app.debug')) {
			$data = [
				'class' => get_class($ex),
				'file'  => $ex->getFile(),
				'line'  => $ex->getLine(),
			];
		}

		// let's figure out what event we are handling now
		$base_config_key = 'response_builder.exception_handler.exception.';
		if (Config::get($base_config_key . 'http_not_found.code', ErrorCode::EX_HTTP_NOT_FOUND) == $error_code) {
			$base_error_code = ErrorCode::EX_HTTP_NOT_FOUND;
		} elseif (Config::get($base_config_key . 'http_service_unavailable.code', ErrorCode::EX_HTTP_SERVICE_UNAVAILABLE) == $error_code) {
			$base_error_code = ErrorCode::EX_HTTP_SERVICE_UNAVAILABLE;
		} elseif (Config::get($base_config_key . 'http_exception.code', ErrorCode::EX_HTTP_EXCEPTION) == $error_code) {
			$base_error_code = ErrorCode::EX_HTTP_EXCEPTION;
		} elseif (Config::get($base_config_key . 'uncaught_exception.code', ErrorCode::EX_UNCAUGHT_EXCEPTION) == $error_code) {
			$base_error_code = ErrorCode::EX_UNCAUGHT_EXCEPTION;
		} else {
			$base_error_code = ErrorCode::NO_ERROR_MESSAGE;
		}

		$key = ErrorCode::getMapping($error_code);
		if (is_null($key)) {
			$key = ErrorCode::getBaseMapping($base_error_code);
		}

		// let's build error message
		$error_message = '';
		$ex_message = trim($ex->getMessage());
		if (Config::get('response_builder.exception_handler.use_exception_message_first', true)) {
			if ($ex_message == '') {
				$ex_message = get_class($ex);
			} else {
				$error_message = $ex_message;
			}
		}

		if ($error_message == '') {
			$error_message = Lang::get($key, [
				'error_code' => $error_code,
				'message'    => $ex_message,
				'class'      => get_class($ex),
			]);
		}

		return ResponseBuilder::errorWithMessageAndData($error_code, $error_message, $data, $http_code);
	}

}
