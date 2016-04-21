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
				case Response::HTTP_NOT_FOUND:
					$result = static::error($ex, 'http_not_found', ErrorCode::EX_HTTP_NOT_FOUND);
					break;

				case Response::HTTP_SERVICE_UNAVAILABLE:
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
			$msg = trim($ex->getMessage());
			if (Config::get('response_builder.exception_handler.include_class_name', false)) {
				$class_name = get_class($ex);
				if ($msg != '') {
					$msg = $class_name . ': ' . $msg;
				} else {
					$msg = $class_name;
				}
			}

			$result = static::error($ex, 'uncaught_exception', HttpResponse::HTTP_INTERNAL_SERVER_ERROR, ['message' => $msg]);
		}

		return $result;
	}

	/**
	 * @param Exception $ex
	 * @param string    $config_base
	 * @param integer   $default_error_code
	 * @param integer   $default_http_code
	 * @param array     $lang_args
	 *
	 * @return Response
	 */
	protected static function error(Exception $ex, $config_base, $default_error_code,
	                                $default_http_code = HttpResponse::HTTP_BAD_REQUEST, array $lang_args = [])
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
				'file' => $ex->getFile(),
				'line' => $ex->getLine(),
			];
		}

		// Check if we got user mapping for the event. If not, fall back to built-in messages

		$key = ErrorCode::getMapping($error_code);
		if (is_null($key)) {
			if (Config::get('response_builder.exception_handler.exception.http_not_found') == $error_code) {
				$key = 'response-builder::builder.http_not_found';
			} elseif (Config::get('response_builder.exception_handler.exception.http_service_unavailable') == $error_code) {
				$key = 'response-builder::builder.service_unavailable';
			} elseif (Config::get('response_builder.exception_handler.exception.http_exception') == $error_code) {
				$key = 'response-builder::builder.http_exception';
			} elseif (Config::get('response_builder.exception_handler.exception.uncaught_exception') == $error_code) {
				$key = 'response-builder::builder.uncaught_exception';
			} else {
				$key = 'response-builder::builder.no_error_message';
			}
		}
		$error_message = Lang::get($key, $lang_args);

		return ResponseBuilder::errorWithMessageAndData($error_code, $error_message, $data, $http_code);
	}
}
