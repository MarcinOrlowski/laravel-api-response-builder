<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

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
class ErrorTest extends TestCase
{
	/**
	 * Check success()
	 *
	 * @return void
	 */
	public function testError()
	{
		// GIVEN random error code
		$api_code = $this->random_api_code;

		// WHEN we report error
		$this->response = ResponseBuilder::error($api_code);

		// THEN returned message contains given error code and mapped message
		$j = $this->getResponseErrorObject($api_code);
		$this->assertEquals($this->random_api_code_message, $j->message);

		// AND no data
		$this->assertNull($j->data);
	}

	/**
	 * Tests error() with various http codes and random payload
	 *
	 * @return void
	 */
	public function testError_WithDataHttpCode()
	{
		$http_codes = [HttpResponse::HTTP_CONFLICT,
		               HttpResponse::HTTP_BAD_REQUEST,
		               HttpResponse::HTTP_FAILED_DEPENDENCY,
		               ResponseBuilder::DEFAULT_HTTP_CODE_ERROR,
		];

		foreach ($http_codes as $http_code) {
			// GIVEN data
			$data = [$this->getRandomString('key') => $this->getRandomString('val')];

			// AND error code
			$api_code = $this->random_api_code;

			// WHEN we report error
			$this->response = ResponseBuilder::error($api_code, null, $data, $http_code);

			// THEN returned message contains given error code and mapped message
			$j = $this->getResponseErrorObject($api_code, $http_code);
			$this->assertEquals($this->random_api_code_message, $j->message);

			// AND passed data
			$this->assertEquals((object)$data, $j->data);
		}
	}

	/**
	 * Tests errorWithData()
	 *
	 * @return void
	 */
	public function testErrorWithData()
	{
		$data = [$this->getRandomString('key') => $this->getRandomString('val')];
		$api_code = $this->random_api_code;
		$this->response = ResponseBuilder::errorWithData($api_code, $data);

		$j = $this->getResponseErrorObject($api_code);
		$this->assertEquals((object)$data, $j->data);
	}

	/**
	 * Tests errorWithDataAndHttpCode()
	 *
	 * @return void
	 */
	public function testErrorWithDataAndHttpCode()
	{
		$http_codes = [HttpResponse::HTTP_CONFLICT,
		               HttpResponse::HTTP_BAD_REQUEST,
		               HttpResponse::HTTP_FAILED_DEPENDENCY,
		               ResponseBuilder::DEFAULT_HTTP_CODE_ERROR,
		];

		foreach ($http_codes as $http_code) {
			$data = [$this->getRandomString('key') => $this->getRandomString('val')];
			$api_code = $this->random_api_code;
			$this->response = ResponseBuilder::errorWithDataAndHttpCode($api_code, $data, $http_code);

			$j = $this->getResponseErrorObject($api_code, $http_code);
			$this->assertEquals((object)$data, $j->data);
		}
	}

	/**
	 * Tests errorWithDataAndHttpCode() with http_code null
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testErrorWithDataAndHttpCode_HttpCodeNull()
	{
//		$this->expectException(\InvalidArgumentException::class);
		ResponseBuilder::errorWithDataAndHttpCode($this->random_api_code, null, null);
	}

	/**
	 * Tests errorWithHttpCode()
	 *
	 * @return void
	 */
	public function testErrorWithHttpCode()
	{
		$http_codes = [HttpResponse::HTTP_CONFLICT,
		               HttpResponse::HTTP_BAD_REQUEST,
		               HttpResponse::HTTP_FAILED_DEPENDENCY,
		               ResponseBuilder::DEFAULT_HTTP_CODE_ERROR,
		];

		foreach ($http_codes as $http_code) {
			$api_code = $this->random_api_code;
			$this->response = ResponseBuilder::errorWithHttpCode($api_code, $http_code);

			$j = $this->getResponseErrorObject($api_code, $http_code);
			$this->assertNull($j->data);
		}
	}

	/**
	 * Tests errorWithHttpCode() with @null as http_code
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testErrorWithHttpCode_NullHttpCode()
	{
		ResponseBuilder::errorWithHttpCode($this->random_api_code, null);
	}

	/**
	 * Tests errorWithMessageAndData()
	 *
	 * @return void
	 */
	public function testErrorWithMessageAndData()
	{
		$data = [$this->getRandomString('key') => $this->getRandomString('val')];
		$api_code = $this->random_api_code;
		$error_message = $this->getRandomString('msg');
		$this->response = ResponseBuilder::errorWithMessageAndData($api_code, $error_message, $data);

		$j = $this->getResponseErrorObject($api_code, ResponseBuilder::DEFAULT_HTTP_CODE_ERROR, $error_message);
		$this->assertEquals($error_message, $j->message);
		$this->assertEquals((object)$data, $j->data);
	}

	/**
	 * Tests errorWithMessageAndDataAndDebug()
	 *
	 * @return void
	 */
	public function testErrorWithMessageAndDataAndDebug()
	{
		$trace_key = \Config::get(ResponseBuilder::CONF_KEY_DEBUG_EX_TRACE_KEY, ResponseBuilder::KEY_TRACE);
		$trace_data = [
			$trace_key => (object)[
				$this->getRandomString('trace_key') => $this->getRandomString('trace_val'),
			],
		];

		$data = [$this->getRandomString('key') => $this->getRandomString('val')];
		$api_code = $this->random_api_code;
		$error_message = $this->getRandomString('msg');

		\Config::set(ResponseBuilder::CONF_KEY_DEBUG_EX_TRACE_ENABLED, true);
		$this->response = ResponseBuilder::errorWithMessageAndDataAndDebug($api_code, $error_message, $data, null, null, $trace_data);

		$j = $this->getResponseErrorObject($api_code, ResponseBuilder::DEFAULT_HTTP_CODE_ERROR, $error_message);
		$this->assertEquals($error_message, $j->message);
		$this->assertEquals((object)$data, $j->data);

		$debug_key = \Config::get(ResponseBuilder::CONF_KEY_DEBUG_DEBUG_KEY, ResponseBuilder::KEY_DEBUG);
//		var_dump((object)$trace_data);
//		var_dump($j->debug_key);
		$this->assertEquals((object)$trace_data, $j->$debug_key);
	}

	/**
	 * Tests errorWithMessage()
	 *
	 * @return void
	 */
	public function testErrorWithMessage()
	{
		$api_code = $this->random_api_code;
		$error_message = $this->getRandomString('msg');
		$this->response = ResponseBuilder::errorWithMessage($api_code, $error_message);

		$j = $this->getResponseErrorObject($api_code, ResponseBuilder::DEFAULT_HTTP_CODE_ERROR, $error_message);
		$this->assertEquals($error_message, $j->message);
		$this->assertNull($j->data);
	}


	/**
	 * Tests error() handling api code with no message mapping
	 *
	 * @return void
	 */
	public function testError_MissingMessageMapping()
	{
		/** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();

		// FIXME we **assume** this is not mapped. But assumptions sucks...
		$api_code = $this->max_allowed_code - 1;
		$this->response = ResponseBuilder::error($api_code);

		$key = $api_codes_class_name::getCodeMessageKey($api_codes_class_name::NO_ERROR_MESSAGE);
		$lang_args = ['api_code' => $api_code];
		$msg = \Lang::get($key, $lang_args);

		$j = $this->getResponseErrorObject($api_code, ResponseBuilder::DEFAULT_HTTP_CODE_ERROR, $msg);
		$this->assertNull($j->data);
	}

	/**
	 * Tests buildErrorResponse() fed with not allowed OK api code
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponse_ApiCodeOK()
	{
		/** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();

		$data = null;
		$http_code = 404;
		$api_code = $api_codes_class_name::OK;
		$lang_args = null;

		$this->callBuildErrorResponse($data, $api_code, $http_code, $lang_args);
	}


	/**
	 * Tests buildErrorResponse() fed with api_code in form of disallowed variable type
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponse_WrongApiCodeType()
	{
		$data = null;
		$http_code = 404;
		$api_code = 'wrong-error-code';
		$lang_args = null;

		$this->callBuildErrorResponse($data, $api_code, $http_code, $lang_args);
	}

	/**
	 * Tests buildErrorResponse() fed with http_code in form of disallowed variable type
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponse_WrongHttpCodeType()
	{
		/** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();

		$data = null;
		$http_code = 'string-is-invalid';
		$api_code = $api_codes_class_name::NO_ERROR_MESSAGE;
		$lang_args = null;

		$this->callBuildErrorResponse($data, $api_code, $http_code, $lang_args);
	}

	/**
	 * Tests buildErrorResponse() fed with @null as http_code
	 *
	 * @return void
	 */
	public function testBuildErrorResponse_NullHttpCode()
	{
		/** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();

		$data = null;
		$http_code = null;
		$api_code = $api_codes_class_name::NO_ERROR_MESSAGE;
		$lang_args = null;

		$this->response = $this->callBuildErrorResponse($data, $api_code, $http_code, $lang_args);

		$http_code = ResponseBuilder::DEFAULT_HTTP_CODE_ERROR;
		$this->assertEquals($http_code, $this->response->getStatusCode());
	}

	/**
	 * Tests buildErrorResponse() fed with http code out of allowed bounds
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponse_TooLowHttpCode()
	{
		/** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();

		$data = null;
		$http_code = 0;
		$api_code = $api_codes_class_name::NO_ERROR_MESSAGE;
		$lang_args = null;

		$this->callBuildErrorResponse($data, $api_code, $http_code, $lang_args);
	}

	/**
	 * Tests buildErrorResponse() fed with wrong lang_args data
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponse_WrongLangArgs()
	{
		/** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();

		$data = null;
		$http_code = 404;
		$api_code = $api_codes_class_name::NO_ERROR_MESSAGE;
		$lang_args = 'string-is-invalid';

		/** @noinspection PhpParamsInspection */
		$this->callBuildErrorResponse($data, $api_code, $http_code, $lang_args);
	}

	/**
	 * Calls protected method buildErrorResponse()
	 *
	 * @param mixed|null $data
	 * @param int|null   $api_code
	 * @param int|null   $http_code
	 * @param mixed|null $lang_args
	 *
	 * @return mixed
	 */
	protected function callBuildErrorResponse($data, $api_code, $http_code, $lang_args)
	{
		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'buildErrorResponse');

		return $method->invokeArgs($obj, [$data,
		                                  $api_code,
		                                  $http_code,
		                                  $lang_args]);
	}

}
