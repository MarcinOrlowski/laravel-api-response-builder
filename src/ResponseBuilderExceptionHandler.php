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
 * Class ResponseBuilderExceptionHandler
 */
class ResponseBuilderExceptionHandler extends ExceptionHandler
{
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException',
	];
	// @codingStandardsIgnoreEnd

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request $request   Request object
	 * @param  \Exception               $exception Exception
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function render(\Illuminate\Http\Request $request, Exception $exception)
	{
		if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
			switch ($exception->getStatusCode()) {
				case Response::HTTP_NOT_FOUND:
					$result = ResponseBuilder::errorWithHttpCode(Config::get('response_builder.exception_handler.unknown_method'),
						$exception->getStatusCode());
					break;

				case Response::HTTP_SERVICE_UNAVAILABLE:
					$result = ResponseBuilder::errorWithHttpCode(Config::get('response_builder.exception_handler.service_in_maintenance'),
						$exception->getStatusCode());
					break;

				default:
					$msg = trim($exception->getMessage());
					if ($msg == '') {
						$msg = '#' . $exception->getStatusCode();
					}

					$result = ResponseBuilder::error(Config::get('response_builder.exception_handler.http_exception'),
						['message' => $msg], null, $exception->getStatusCode());
					break;
			}
		} else {
			$msg = get_class($exception);
			if (trim($exception->getMessage()) != '') {
				$msg .= ': ' . $exception->getMessage();
			}

			$result = ResponseBuilder::error(Config::get('response_builder.exception_handler.uncaught_exception'),
				['message' => $msg], null, 500);
		}

		return $result;
	}

}
