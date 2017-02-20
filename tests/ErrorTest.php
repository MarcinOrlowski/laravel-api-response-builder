<?php

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

use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ErrorTest extends ResponseBuilderTestCase
{

	/**
	 * Check success()
	 */
	public function testError()
	{
		// GIVEN random error code
		$error_code = $this->random_error_code;

		// WHEN we report error
		$this->response = ResponseBuilder::error($error_code);

		// THEN returned message contains given error code and mapped message
		$j = $this->getResponseErrorObject($error_code);
		$this->assertEquals($this->error_message_map[ $error_code ], $j->message);

		// AND no data
		$this->assertNull($j->data);
	}

	public function testError_WithDataHttpCode()
	{
		$http_codes = [HttpResponse::HTTP_CONFLICT,
		               HttpResponse::HTTP_BAD_REQUEST,
		               HttpResponse::HTTP_FAILED_DEPENDENCY,
		               ResponseBuilder::DEFAULT_HTTP_CODE_ERROR,
		];

		foreach($http_codes as $http_code) {
			// GIVEN data
			$data = [$this->getRandomString('key') => $this->getRandomString('val')];

			// AND error code
			$error_code = $this->random_error_code;

			// WHEN we report error
			$this->response = ResponseBuilder::error($error_code, null, $data, $http_code);

			// THEN returned message contains given error code and mapped message
			$j = $this->getResponseErrorObject($error_code, $http_code);
			$this->assertEquals($this->error_message_map[ $error_code ], $j->message);

			// AND passed data
			$this->assertEquals((object)$data, $j->data);
		}
	}

	public function testErrorWithData()
	{
		$data = [$this->getRandomString('key') => $this->getRandomString('val')];
		$error_code = $this->random_error_code;
		$this->response = ResponseBuilder::errorWithData($error_code, $data);

		$j = $this->getResponseErrorObject($error_code);
		$this->assertEquals((object)$data, $j->data);
	}

	public function testErrorWithDataAndHttpCode()
	{
		$http_codes = [HttpResponse::HTTP_CONFLICT,
		               HttpResponse::HTTP_BAD_REQUEST,
		               HttpResponse::HTTP_FAILED_DEPENDENCY,
		               ResponseBuilder::DEFAULT_HTTP_CODE_ERROR,
		];

		foreach($http_codes as $http_code) {
			$data = [$this->getRandomString('key') => $this->getRandomString('val')];
			$error_code = $this->random_error_code;
			$this->response = ResponseBuilder::errorWithDataAndHttpCode($error_code, $data, $http_code);

			$j = $this->getResponseErrorObject($error_code, $http_code);
			$this->assertEquals((object)$data, $j->data);
		}
	}

	public function testErrorWithDataAndHttpCode_Null()
	{
		$this->expectException(\InvalidArgumentException::class);
		ResponseBuilder::errorWithDataAndHttpCode($this->random_error_code, null, null);
	}

	public function testErrorWithHttpCode()
	{
		$http_codes = [HttpResponse::HTTP_CONFLICT,
		               HttpResponse::HTTP_BAD_REQUEST,
		               HttpResponse::HTTP_FAILED_DEPENDENCY,
		               ResponseBuilder::DEFAULT_HTTP_CODE_ERROR,
		];

		foreach($http_codes as $http_code) {
			$error_code = $this->random_error_code;
			$this->response = ResponseBuilder::errorWithHttpCode($error_code, $http_code);

			$j = $this->getResponseErrorObject($error_code, $http_code);
			$this->assertNull($j->data);
		}
	}

	public function testErrorWithHttpCode_NullHttpCode()
	{
		$this->expectException(\InvalidArgumentException::class);

		ResponseBuilder::errorWithHttpCode($this->random_error_code, null);
	}


	public function testErrorWithMessageAndData()
	{
		$data = [$this->getRandomString('key') => $this->getRandomString('val')];
		$error_code = $this->random_error_code;
		$error_message = $this->getRandomString('msg');
		$this->response = ResponseBuilder::errorWithMessageAndData($error_code, $error_message, $data);

		$j = $this->getResponseErrorObject($error_code, ResponseBuilder::DEFAULT_HTTP_CODE_ERROR, $error_message);
		$this->assertEquals($error_message, $j->message);
		$this->assertEquals((object)$data, $j->data);
	}

	public function testErrorWithMessage()
	{
		$error_code = $this->random_error_code;
		$error_message = $this->getRandomString('msg');
		$this->response = ResponseBuilder::errorWithMessage($error_code, $error_message);

		$j = $this->getResponseErrorObject($error_code, ResponseBuilder::DEFAULT_HTTP_CODE_ERROR, $error_message);
		$this->assertEquals($error_message, $j->message);
		$this->assertNull($j->data);
	}


	public function testError_MissingMessageMapping()
	{
		$api_codes = $this->getApiCodesClassName();

		$error_code = $this->random_error_code + 1;
		$this->response = ResponseBuilder::error($error_code);

		$key = $api_codes::getMapping($api_codes::NO_ERROR_MESSAGE);
		$lang_args = ['error_code' => $error_code];
		$msg = \Lang::get($key, $lang_args);

		$j = $this->getResponseErrorObject($error_code, ResponseBuilder::DEFAULT_HTTP_CODE_ERROR, $msg);
		$this->assertNull($j->data);
	}

	public function testBuildErrorResponse_ApiCodeOK()
	{
		$this->expectException(\InvalidArgumentException::class);

		$api_codes = $this->getApiCodesClassName();

		$data = null;
		$http_code = 404;
		$error_code = $api_codes::OK;
		$lang_args = null;

		$this->callBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}


	public function testBuildErrorResponse_WrongErrorCode()
	{
		$this->expectException(\InvalidArgumentException::class);

		$data = null;
		$http_code = 404;
		$error_code = 'wrong-error-code';
		$lang_args = null;

		$this->callBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	public function testBuildErrorResponse_WrongHttpCode()
	{
		$this->expectException(\InvalidArgumentException::class);

		$api_codes = $this->getApiCodesClassName();

		$data = null;
		$http_code = 'string-is-invalid';
		$error_code = $api_codes::NO_ERROR_MESSAGE;
		$lang_args = null;

		$this->callBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	public function testBuildErrorResponse_NullHttpCode()
	{
		$api_codes = $this->getApiCodesClassName();

		$data = null;
		$http_code = null;
		$error_code = $api_codes::NO_ERROR_MESSAGE;
		$lang_args = null;

		$this->response = $this->callBuildErrorResponse($data, $error_code, $http_code, $lang_args);

		$http_code = ResponseBuilder::DEFAULT_HTTP_CODE_ERROR;
		$this->assertEquals($http_code, $this->response->getStatusCode());
	}

	public function testBuildErrorResponse_TooLowHttpCode()
	{
		$this->expectException(\InvalidArgumentException::class);

		$api_codes = $this->getApiCodesClassName();

		$data = null;
		$http_code = 0;
		$error_code = $api_codes::NO_ERROR_MESSAGE;
		$lang_args = null;

		$this->callBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	public function testBuildErrorResponse_WrongLangArgs()
	{
		$api_codes = $this->getApiCodesClassName();

		$data = null;
		$http_code = 404;
		$error_code = $api_codes::NO_ERROR_MESSAGE;
		$lang_args = 'string-is-invalid';

		$this->expectException(\InvalidArgumentException::class);
		$this->callBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	protected function callBuildErrorResponse($data, $error_code, $http_code, $lang_args)
	{
		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'buildErrorResponse');

		return $method->invokeArgs($obj, [$data, $error_code, $http_code, $lang_args]);
	}

}