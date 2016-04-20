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
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;

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
					$result = static::error($ex, Config::get('response_builder.exception_handler.exception.http_not_found'));
					break;

				case Response::HTTP_SERVICE_UNAVAILABLE:
					$result = static::error($ex, Config::get('response_builder.exception_handler.exception.http_service_unavailable'));
					break;

				default:
					$msg = trim($ex->getMessage());
					if ($msg == '') {
						$msg = 'Exception code #' . $ex->getStatusCode();
					}

					$result = static::error($ex, Config::get('response_builder.exception_handler.exception.http_exception'),
						['message' => $msg]);
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

			$result = static::error($ex, Config::get('response_builder.exception_handler.exception.uncaught_exception'),
				['message' => $msg], Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		return $result;
	}

	/**
	 * @param Exception $ex
	 * @param integer   $error_code
	 * @param array     $lang_args
	 * @param integer   $http_code
	 *
	 * @return Response
	 */
	protected static function error(Exception $ex, $error_code, array $lang_args = [], $http_code = 0)
	{
		if ($http_code == 0) {
			$http_code = $ex->getStatusCode();
		}

		$data = [];
		if (Config::get('app.debug')) {
			$data = [
				'file' => $ex->getFile(),
				'line' => $ex->getLine(),
			];
		}

		return ResponseBuilder::error($error_code, $lang_args, $data, $http_code);
	}
}
