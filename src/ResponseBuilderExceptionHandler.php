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
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception $e
	 *
	 * @return void
	 */
	public function report(Exception $e) {
		parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Exception               $e
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e) {

		if( $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException ) {
			switch($e->getStatusCode()) {
				case Response::HTTP_NOT_FOUND: {
					$result = ResponseBuilder::errorWithHttpCode(Config::get('response_builder.exception_handler.unknown_method'), $e->getStatusCode());
				}
					break;

				case Response::HTTP_SERVICE_UNAVAILABLE: {
					$result = ResponseBuilder::errorWithHttpCode(Config::get('response_builder.exception_handler.service_in_maintenance'), $e->getStatusCode());
				}
					break;

				default: {
					$msg = trim($e->getMessage());
					if( $msg == '' ) {
						$msg = '#' . $e->getStatusCode();
					}

					$result = ResponseBuilder::error(Config::get('response_builder.exception_handler.http_exception'), ['message' => $msg], null, $e->getStatusCode());
				}
					break;
			}
		} else {
			$msg = get_class($e);
			if( trim($e->getMessage()) != '' ) {
				$msg .= ': ' . $e->getMessage();
			}

			$result = ResponseBuilder::error(Config::get('response_builder.exception_handler.uncaught_exception'), ['message' => $msg], null, 500);
		}

		return $result;
	}

}
