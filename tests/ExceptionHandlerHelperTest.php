<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlerHelper;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Mockery\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionHandlerHelperTest extends TestCase
{
	/**
	 * Check exception handler behavior when given different types of exception.
	 *
	 * @return void
	 */
	public function testRender_HttpException(): void
	{
		$codes = [
			[
				'exception_class'           => HttpException::class,
				'exception_status_code'     => HttpResponse::HTTP_NOT_FOUND,
				'exception_type'            => ExceptionHandlerHelper::TYPE_HTTP_NOT_FOUND,
				'default_response_api_code' => BaseApiCodes::EX_HTTP_NOT_FOUND,
			],
			[
				'exception_class'           => HttpException::class,
				'exception_status_code'     => HttpResponse::HTTP_SERVICE_UNAVAILABLE,
				'exception_type'            => ExceptionHandlerHelper::TYPE_HTTP_SERVICE_UNAVAILABLE,
				'default_response_api_code' => BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE,
			],
			[
				'exception_class'           => HttpException::class,
				'exception_status_code'     => HttpResponse::HTTP_UNAUTHORIZED,
				'exception_type'            => ExceptionHandlerHelper::TYPE_HTTP_UNAUTHORIZED,
				'default_response_api_code' => BaseApiCodes::EX_AUTHENTICATION_EXCEPTION,
			],
			[
				'exception_class'           => HttpException::class,
				'exception_status_code'     => HttpResponse::HTTP_BAD_REQUEST,
				'exception_type'            => ExceptionHandlerHelper::TYPE_DEFAULT,
				'default_response_api_code' => BaseApiCodes::EX_HTTP_EXCEPTION,
			],
		];
		foreach ($codes as $params) {
			$exception_type = $params['exception_type'];

			$base_config = 'response_builder.exception_handler.exception';
			$response_api_code = \Config::get("{$base_config}.{$exception_type}.code", $params['default_response_api_code']);
//			$http_code = \Config::get("{$base_config}.{$exception_type}.http_code", 0);

			$key = BaseApiCodes::getCodeMessageKey($response_api_code);
			if ($key === null) {
				$key = BaseApiCodes::getReservedCodeMessageKey($response_api_code);
			}

			$exception_class_under_test = $params['exception_class'];

			/** @var \Exception $exception */
			$exception = null;
			/** @noinspection DegradedSwitchInspection */
			switch ($exception_class_under_test) {
				case HttpException::class:
					$exception = new $exception_class_under_test($params['exception_status_code']);
					break;

				default:
					$this->fail("Unknown exception class: '{$exception_class_under_test}'");
					break;
			}

//			// check if this is valid HTTP error code
//			if ($http_code === 0) {
//				// no code, let's try getting the exception status
//				if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
//					$http_code = $exception->getStatusCode();
//				} else {
//					$http_code = $exception->getCode();
//				}
//			}
//
//			// can it be considered valid HTTP error code?
//			if ($http_code < 400) {
//				$http_code = ResponseBuilder::DEFAULT_HTTP_CODE_ERROR;
//			}

			// hand the exception to the handler and examine its response JSON
			$eh = new ExceptionHandlerHelper();
			$eh_response = $eh->render(null, $exception);
			$eh_response_json = json_decode($eh_response->getContent(), false);

			$this->assertValidResponse($eh_response_json);
			$this->assertNull($eh_response_json->data);

			$ex_message = trim($exception->getMessage());
			if (\Config::get('response_builder.exception_handler.use_exception_message_first', true)) {
				if ($ex_message === '') {
					$ex_message = get_class($exception);
				}
			}

			$error_message = \Lang::get($key, [
				'response_api_code' => $response_api_code,
				'message'           => $ex_message,
				'class'             => get_class($exception),
			]);

			$this->assertEquals($error_message, $eh_response_json->message);
			$this->assertEquals($params['exception_status_code'], $eh_response->getStatusCode(),
				sprintf('Unexpected HTTP code value for "%s".', $params['exception_status_code']));
		}
	}

	/**
	 * Tests if optional debug info is properly added to JSON response
	 *
	 * @return void
	 */
	public function testError_DebugTrace(): void
	{
		\Config::set(ResponseBuilder::CONF_KEY_DEBUG_EX_TRACE_ENABLED, true);

		$exception = new \RuntimeException();

		$eh = new ExceptionHandlerHelper();

		$j = json_decode($eh->render(null, $exception)->getContent(), false);

		$this->assertValidResponse($j);
		$this->assertNull($j->data);

		$key = ResponseBuilder::KEY_DEBUG;
		$this->assertObjectHasAttribute($key, $j, sprintf("No '{key}' element in response structure found"));

		// Note that we do not check what debug node contains. It's on purpose as whatever ends up there
		// is not generated by us, so may change any time.
	}
}
