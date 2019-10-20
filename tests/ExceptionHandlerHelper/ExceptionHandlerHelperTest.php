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

use Illuminate\Auth\AuthenticationException;
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
	 *
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function testRenderMethodWithHttpException(): void
	{
		$codes = [
			ExceptionHandlerHelper::TYPE_HTTP_NOT_FOUND_KEY           => [
				'exception_class'       => HttpException::class,
				'expected_http_code'    => HttpResponse::HTTP_NOT_FOUND,
				'expected_api_code'     => BaseApiCodes::EX_HTTP_NOT_FOUND(),
				'do_message_validation' => true,
				'has_data_node'         => false,
			],
			ExceptionHandlerHelper::TYPE_HTTP_SERVICE_UNAVAILABLE_KEY => [
				'exception_class'       => HttpException::class,
				'expected_http_code'    => HttpResponse::HTTP_SERVICE_UNAVAILABLE,
				'expected_api_code'     => BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE(),
				'do_message_validation' => true,
				'has_data_node'         => false,
			],
			ExceptionHandlerHelper::TYPE_HTTP_EXCEPTION_KEY           => [
				'exception_class'       => HttpException::class,
				'expected_http_code'    => HttpResponse::HTTP_BAD_REQUEST,
				'expected_api_code'     => BaseApiCodes::EX_HTTP_EXCEPTION(),
				'do_message_validation' => true,
				'has_data_node'         => false,
			],
			ExceptionHandlerHelper::TYPE_UNCAUGHT_EXCEPTION_KEY       => [
				'exception_class'       => \RuntimeException::class,
				'expected_http_code'    => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
				'expected_api_code'     => BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
				'do_message_validation' => true,
				'has_data_node'         => false,
			],
			ExceptionHandlerHelper::TYPE_HTTP_UNAUTHORIZED_KEY        => [
				'exception_class'       => HttpException::class,
				'expected_http_code'    => HttpResponse::HTTP_UNAUTHORIZED,
				'expected_api_code'     => BaseApiCodes::EX_AUTHENTICATION_EXCEPTION(),
				'do_message_validation' => true,
				'has_data_node'         => false,
			],
			ExceptionHandlerHelper::TYPE_VALIDATION_EXCEPTION_KEY     => [
				'exception_class'       => ValidationException::class,
				'expected_http_code'    => HttpResponse::HTTP_BAD_REQUEST,
				'expected_api_code'     => BaseApiCodes::EX_VALIDATION_EXCEPTION(),
				'do_message_validation' => false,
				'has_data_node'         => true,
			],
		];

		foreach ($codes as $exception_type => $params) {
			$this->doTestSingleException($exception_type, $params['exception_class'],
				$params['expected_http_code'], $params['expected_api_code'],
				$params['do_message_validation'], $params['has_data_node']);
		}
	}

	/**
	 * Handles single exception testing.
	 *
	 * @param string $exception_config_key           ResponseBuilder's config key for this particular exception.
	 * @param string $exception_class                Name of the class of exception to be constructed.
	 * @param int    $expected_http_code             Expected response HTTP code
	 * @param int    $expected_api_code              Expected response API code
	 * @param bool   $validate_response_message_text Set to @true, to validate returned response message with
	 *                                               current localization.
	 * @param bool   $expected_data_node             Set to @true if response is expected to have non null `data` node.
	 *
	 * @return void
	 *
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	protected function doTestSingleException(string $exception_config_key, string $exception_class,
	                                         int $expected_http_code, int $expected_api_code,
	                                         bool $validate_response_message_text = true,
	                                         bool $expected_data_node = false): void
	{
		$key = BaseApiCodes::getCodeMessageKey($expected_api_code);
		$expect_data_node_null = true;
		switch ($exception_class) {
			case HttpException::class:
				$exception = new $exception_class($expected_http_code);
				break;

			case ValidationException::class:
				$data = ['title' => ''];
				$rules = ['title' => 'required|min:10|max:255'];
				/** @noinspection PhpUnhandledExceptionInspection */
				$validator = app('validator')->make($data, $rules);
				$exception = new ValidationException($validator);
				$expect_data_node_null = false;
				break;

			default:
				$exception = new $exception_class(null, $expected_http_code);
				break;
		}

		// hand the exception to the handler and examine its response JSON
		$eh_response = ExceptionHandlerHelper::render(null, $exception);
		$eh_response_json = json_decode($eh_response->getContent(), false);

		$this->assertValidResponse($eh_response_json);
		if ($expect_data_node_null) {
			$this->assertNull($eh_response_json->data);
		}

		$ex_message = trim($exception->getMessage());
		if ($ex_message === '') {
			$ex_message = get_class($exception);
		}

		/** @noinspection PhpUndefinedClassInspection */
		$error_message = \Lang::get($key, [
			'response_api_code' => $expected_api_code,
			'message'           => $ex_message,
			'class'             => get_class($exception),
		]);

		if ($validate_response_message_text) {
			$this->assertEquals($error_message, $eh_response_json->message);
		}
		$this->assertEquals($expected_http_code, $eh_response->getStatusCode(),
			sprintf('Unexpected HTTP code value for "%s".', $exception_config_key));
		if ($expected_data_node) {
			$data = $eh_response_json->{ResponseBuilder::KEY_DATA};
			$this->assertNotNull($data);
			$this->assertObjectHasAttribute(ResponseBuilder::KEY_MESSAGES, $data);
			$this->assertIsObject($data->{ResponseBuilder::KEY_MESSAGES});
		}
	}

	/**
	 * Tests if optional debug info is properly added to JSON response
	 *
	 * @return void
	 */
	public function testErrorMethodWithDebugTrace(): void
	{
		/** @noinspection PhpUndefinedClassInspection */
		\Config::set(ResponseBuilder::CONF_KEY_DEBUG_EX_TRACE_ENABLED, true);

		$exception = new \RuntimeException();

		$j = json_decode(ExceptionHandlerHelper::render(null, $exception)->getContent(), false);

		$this->assertValidResponse($j);
		$this->assertNull($j->data);

		$key = ResponseBuilder::KEY_DEBUG;
		$this->assertObjectHasAttribute($key, $j, sprintf("No '{key}' element in response structure found"));

		// Note that we do not check what debug node contains. It's on purpose as whatever ends up there
		// is not generated by us, so may change any time.
	}

	/**
	 * @return void
	 *
	 * @throws \ReflectionException
	 */
	public function testUnauthenticated(): void
	{
		$exception = new AuthenticationException();

		$obj = new ExceptionHandlerHelper();
		$eh_response = $this->callProtectedMethod($obj, 'unauthenticated', [null,
		                                                                    $exception]);

		$response = json_decode($eh_response->getContent(), false);

		$this->assertValidResponse($response);
		$this->assertNull($response->data);
		$this->assertEquals(BaseApiCodes::EX_AUTHENTICATION_EXCEPTION(), $response->{ResponseBuilder::KEY_CODE});
		$this->assertEquals($exception->getMessage(), $response->{ResponseBuilder::KEY_MESSAGE});
	}
}
