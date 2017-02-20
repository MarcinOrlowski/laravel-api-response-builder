<?php

use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use MarcinOrlowski\ResponseBuilder\ErrorCode;
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
		               HttpResponse::HTTP_FAILED_DEPENDENCY];

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
		               HttpResponse::HTTP_FAILED_DEPENDENCY];

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
		               HttpResponse::HTTP_FAILED_DEPENDENCY];

		foreach($http_codes as $http_code) {
			$error_code = $this->random_error_code;
			$this->response = ResponseBuilder::errorWithHttpCode($error_code, $http_code);

			$j = $this->getResponseErrorObject($error_code, $http_code);
			$this->assertNull($j->data);
		}
	}

	public function testErrorWithHttpCode_Null()
	{
		$this->expectException(\InvalidArgumentException::class);
		ResponseBuilder::errorWithHttpCode($this->random_error_code, null);
	}


	public function testErrorWithMessageAndData() {
		$data = [$this->getRandomString('key') => $this->getRandomString('val')];
		$error_code = $this->random_error_code;
		$error_message = $this->getRandomString('msg');
		$this->response = ResponseBuilder::errorWithMessageAndData($error_code, $error_message, $data);

		$j = $this->getResponseErrorObject($error_code, HttpResponse::HTTP_BAD_REQUEST, $error_message);
		$this->assertEquals($error_message, $j->message);
		$this->assertEquals((object)$data, $j->data);
	}

	public function testErrorWithMessage() {
		$error_code = $this->random_error_code;
		$error_message = $this->getRandomString('msg');
		$this->response = ResponseBuilder::errorWithMessage($error_code, $error_message);

		$j = $this->getResponseErrorObject($error_code, HttpResponse::HTTP_BAD_REQUEST, $error_message);
		$this->assertEquals($error_message, $j->message);
		$this->assertNull($j->data);
	}


	public function testErrorWithNonexistingErrorCodeMessageMapping() {
		$error_code = $this->random_error_code + 1;
		$this->response = ResponseBuilder::error($error_code);
		$j = $this->getResponseErrorObject($error_code);

		$key = ErrorCode::getMapping(ErrorCode::NO_ERROR_MESSAGE);
		$lang_args = ['error_code' => $error_code];
		$this->assertEquals(\Lang::get($key, $lang_args), $j->message);
	}


	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponseWrongErrorCode() {
		$data = null;
		$http_code = 404;
		$error_code = 'wrong-error-code';
		$lang_args = null;

		$this->validateBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponseWrongHttpCode() {
		$data = null;
		$http_code = 'string-is-invalid';
		$error_code = ErrorCode::NO_ERROR_MESSAGE;
		$lang_args = null;

		$this->validateBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	public function testBuildErrorResponseWithNullHttpCode() {
		$data = null;
		$http_code = null;
		$error_code = ErrorCode::NO_ERROR_MESSAGE;
		$lang_args = null;

		$this->validateBuildErrorResponse($data, $error_code, $http_code, $lang_args);

		$http_code = ResponseBuilder::DEFAULT_HTTP_CODE_ERROR;
		$this->assertEquals($http_code, $this->response->getStatusCode());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponseWithTooLowHttpCode() {
		$data = null;
		$http_code = 0;
		$error_code = ErrorCode::NO_ERROR_MESSAGE;
		$lang_args = null;

		$this->validateBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	public function testBuildErrorResponseWithWrongLangArgs() {
		$data = null;
		$http_code = 404;
		$error_code = ErrorCode::NO_ERROR_MESSAGE;
		$lang_args = 'string-is-invalid';

		$this->expectException(\InvalidArgumentException::class);
		$this->validateBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	protected function validateBuildErrorResponse($data, $error_code, $http_code, $lang_args) {
		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'buildErrorResponse');

		$this->response = $method->invokeArgs($obj, [$data, $error_code, $http_code, $lang_args]);
	}

}