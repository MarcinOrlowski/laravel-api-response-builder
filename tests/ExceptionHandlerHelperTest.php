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

use Illuminate\Validation\ValidationException;
use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlerHelper;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
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
			ExceptionHandlerHelper::TYPE_HTTP_NOT_FOUND_KEY           => [
				'exception_class'           => HttpException::class,
				'default_http_code'         => HttpResponse::HTTP_NOT_FOUND,
				'default_response_api_code' => BaseApiCodes::EX_HTTP_NOT_FOUND,
			],
			ExceptionHandlerHelper::TYPE_HTTP_SERVICE_UNAVAILABLE_KEY => [
				'exception_class'           => HttpException::class,
				'default_http_code'         => HttpResponse::HTTP_SERVICE_UNAVAILABLE,
				'default_response_api_code' => BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE,
			],
			ExceptionHandlerHelper::TYPE_HTTP_EXCEPTION_KEY           => [
				'exception_class'           => HttpException::class,
				'default_http_code'         => HttpResponse::HTTP_BAD_REQUEST,
				'default_response_api_code' => BaseApiCodes::EX_HTTP_EXCEPTION,
			],
			ExceptionHandlerHelper::TYPE_UNCAUGHT_EXCEPTION_KEY       => [
				'exception_class'           => \RuntimeException::class,
				'default_http_code'         => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
				'default_response_api_code' => BaseApiCodes::EX_UNCAUGHT_EXCEPTION,
			],
			ExceptionHandlerHelper::TYPE_HTTP_UNAUTHORIZED_KEY        => [
				'exception_class'           => HttpException::class,
				'default_http_code'         => HttpResponse::HTTP_UNAUTHORIZED,
				'default_response_api_code' => BaseApiCodes::EX_AUTHENTICATION_EXCEPTION,
			],

			ExceptionHandlerHelper::TYPE_VALIDATION_EXCEPTION_KEY => [
				'exception_class'           => ValidationException::class,
				'default_http_code'         => HttpResponse::HTTP_BAD_REQUEST,
				'default_response_api_code' => BaseApiCodes::EX_VALIDATION_EXCEPTION,
			],

		];
		foreach ($codes as $exception_type => $params) {
			$base_config_key = 'response_builder.exception_handler.exception';
			$response_api_code = \Config::get("{$base_config_key}.{$exception_type}.code", $params['default_response_api_code']);
			$http_code = \Config::get("{$base_config_key}.{$exception_type}.http_code", $params['default_http_code']);

			$key = BaseApiCodes::getCodeMessageKey($response_api_code);
			if ($key === null) {
				$key = BaseApiCodes::getReservedCodeMessageKey($response_api_code);
			}

			$expect_data_node_null = true;
			switch ($params['exception_class']) {
				case HttpException::class:
					$exception = new $params['exception_class']($http_code);
					break;

				case ValidationException::class:
					$rules = ['title' => 'required|min:10|max:255'];
					$data = ['title' => ''];
					$validator = app('validator')->make($data, $rules);
					$exception = new ValidationException($validator);
					$expect_data_node_null = false;
					break;

				default:
					$exception = new $params['exception_class'](null, $http_code);
					break;
			}

			// hand the exception to the handler and examine its response JSON
			$eh = new ExceptionHandlerHelper();
			$eh_response = $eh->render(null, $exception);
			$eh_response_json = json_decode($eh_response->getContent(), false);

			$this->assertValidResponse($eh_response_json);
			if ($expect_data_node_null) {
				$this->assertNull($eh_response_json->data);
			}

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
			$this->assertEquals($http_code, $eh_response->getStatusCode(),
				sprintf('Unexpected HTTP code value for "%s".', $http_code));
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
