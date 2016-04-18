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
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Config;

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
	 * @return \Illuminate\Http\Response
	 */
	public static function render($request, Exception $exception)
	{
		if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
			switch ($exception->getStatusCode()) {
				case Response::HTTP_NOT_FOUND:
					$result = ResponseBuilder::errorWithHttpCode(Config::get('response_builder.exception_handler.exception.http_not_found'),
						$exception->getStatusCode());
					break;

				case Response::HTTP_SERVICE_UNAVAILABLE:
					$result = ResponseBuilder::errorWithHttpCode(Config::get('response_builder.exception_handler.exception.http_service_unavailable'),
						$exception->getStatusCode());
					break;

				default:
					$msg = trim($exception->getMessage());
					if ($msg == '') {
						$msg = 'Exception code #' . $exception->getStatusCode();
					}

					$result = ResponseBuilder::error(Config::get('response_builder.exception_handler.exception.http_exception'),
						['message' => $msg], null, $exception->getStatusCode());
					break;
			}
		} else {
			$msg = trim($exception->getMessage());
			if (Config::get('response_builder.exception_handler.include_class_name', false)) {
				$class_name = get_class($exception);
				if ($msg != '') {
					$msg = $class_name . ': ' . $msg;
				} else {
					$msg = $class_name;
				}
			}

			$result = ResponseBuilder::error(Config::get('response_builder.exception_handler.exception.uncaught_exception'),
				['message' => $msg], null, 500);
		}

		return $result;
	}

}
