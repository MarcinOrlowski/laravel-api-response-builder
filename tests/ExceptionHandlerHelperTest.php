<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlerHelper;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Mockery\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
class ExceptionHandlerHelperTest extends TestCase
{
	/**
	 * Check success()
	 *
	 * @return void
	 */
	public function testRender_HttpException()
	{
		$codes = [

			[
				'class' => HttpException::class,
				'status_code'      => HttpResponse::HTTP_NOT_FOUND,
				'exception_type'   => ExceptionHandlerHelper::TYPE_HTTP_NOT_FOUND,
				'default_api_code' => BaseApiCodes::EX_HTTP_NOT_FOUND,
			],
			[
				'class' => HttpException::class,
				'status_code'      => HttpResponse::HTTP_SERVICE_UNAVAILABLE,
				'exception_type'   => ExceptionHandlerHelper::TYPE_HTTP_SERVICE_UNAVAILABLE,
				'default_api_code' => BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE,
			],
			[
				'class' => HttpException::class,
				'status_code'      => HttpResponse::HTTP_UNAUTHORIZED,
				'exception_type'   => ExceptionHandlerHelper::TYPE_HTTP_UNAUTHORIZED,
				'default_api_code' => BaseApiCodes::EX_AUTHENTICATION_EXCEPTION,
			],
			[
				'class' => HttpException::class,
				'status_code'      => 400,
				'exception_type'   => ExceptionHandlerHelper::TYPE_DEFAULT,
				'default_api_code' => BaseApiCodes::EX_HTTP_EXCEPTION,
			],

//			[
//				'class' => \RuntimeException::class,
//				'status_code'      => 400,
//				'exception_type'   => ExceptionHandlerHelper::TYPE_UNCAUGHT_EXCEPTION,
//				'default_api_code' => BaseApiCodes::EX_UNCAUGHT_EXCEPTION,
//			],

		];
		foreach ($codes as $params) {
			$status_code = $params['status_code'];
			$exception_type = $params['exception_type'];
			$default_api_code = $params['default_api_code'];

			$base_config = 'response_builder.exception_handler.exception';
			$api_code = \Config::get("{$base_config}.{$exception_type}.code", $default_api_code);
			$http_code = \Config::get("{$base_config}.{$exception_type}.http_code", 0);

			$key = BaseApiCodes::getCodeMessageKey($api_code);
			if ($key === null) {
				$key = BaseApiCodes::getReservedCodeMessageKey($base_api_code);
			}

			$eh = new ExceptionHandlerHelper();

			$cls = $params['class'];
			switch ($cls) {
				case HttpException::class:
					$exception = new $cls($status_code);
					break;

				default:
					$this->fail("Unknown exception class: '{$cls}'");
					break;
			}


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
				$http_code = ResponseBuilder::DEFAULT_HTTP_CODE_ERROR;
			}


			$j = json_decode($eh->render(null, $exception)->getContent());

			$this->validateResponseStructure($j);
			$this->assertNull($j->data);

			$ex_message = trim($exception->getMessage());
			if (\Config::get('response_builder.exception_handler.use_exception_message_first', true)) {
				if ($ex_message === '') {
					$ex_message = get_class($exception);
				} else {
					$error_message = $ex_message;
				}
			}

			$error_message = \Lang::get($key, [
				'api_code' => $api_code,
				'message'  => $ex_message,
				'class'    => get_class($exception),
			]);

			$this->assertEquals($j->message, $error_message);
		}
	}


	/**
	 * Tests if optional debug info is properly added to JSON response
	 *
	 * @return void
	 */
	public function testError_DebugTrace()
	{
		\Config::set(ResponseBuilder::CONF_KEY_DEBUG_EX_TRACE_ENABLED, true);

		$exception = new \RuntimeException();

		$eh = new ExceptionHandlerHelper();

		$j = json_decode($eh->render(null, $exception)->getContent());
	}

}
