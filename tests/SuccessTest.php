<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
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
class SuccessTest extends TestCase
{
	/**
	 * Check success()
	 *
	 * @return void
	 */
	public function testSuccess()
	{
		$this->response = ResponseBuilder::success();
		$j = $this->getResponseSuccessObject(BaseApiCodes::OK);

		$this->assertNull($j->data);
		$this->assertEquals(\Lang::get(BaseApiCodes::getCodeMessageKey(BaseApiCodes::OK)), $j->message);
	}

	public function testSuccess_EncodingOptions()
	{
		$test_string = 'ąćę';
		$test_string_escaped = $this->escape8($test_string);

		// source data
		$data = ['test' => $test_string];

		// check if it returns escaped
		// ensure config is different from what we want
		\Config::set('encoding_options', JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE);

		$encoding_options = JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT;
		$resp = ResponseBuilder::success($data, BaseApiCodes::OK, null, null, $encoding_options);

		$matches = [];
		$this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $resp->getContent(), $matches));
		$result_escaped = $matches[1];
		$this->assertEquals($test_string_escaped, $result_escaped);


		// check if it returns unescaped
		// ensure config is different from what we want
		\Config::set('encoding_options', JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);

		$encoding_options = JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE;
		$resp = ResponseBuilder::success($data, BaseApiCodes::OK, null, null, $encoding_options);

		$matches = [];
		$this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $resp->getContent(), $matches));
		$result_unescaped = $matches[1];
		$this->assertEquals($test_string, $result_unescaped);

		// this one is just in case...
		$this->assertNotEquals($result_escaped, $result_unescaped);
	}

	/**
	 * Tests success() with custom API code no custom message
	 *
	 * @return void
	 */
	public function testSuccess_ApiCode_NoCustomMessage()
	{
		\Config::set(ResponseBuilder::CONF_KEY_MAP, []);
		$api_code = mt_rand($this->min_allowed_code, $this->max_allowed_code);

		$this->response = ResponseBuilder::success(null, $api_code);
		$j = $this->getResponseSuccessObject($api_code);

		$this->assertNull($j->data);
	}

	/**
	 * Tests success() with custom API code and no custom message mapping
	 *
	 * @return void
	 */
	public function testSuccess_ApiCode_CustomMessage()
	{
		$this->response = ResponseBuilder::success(null, $this->random_api_code);
		$j = $this->getResponseSuccessObject($this->random_api_code);

		$this->assertNull($j->data);
	}


	/**
	 * Tests success() with custom API code and custom message
	 *
	 * @return void
	 */
	public function testSuccess_ApiCode_CustomMessageLang()
	{
		// for simplicity let's reuse existing message that is using placeholder
		\Config::set(ResponseBuilder::CONF_KEY_MAP, [
			$this->random_api_code => BaseApiCodes::getCodeMessageKey(BaseApiCodes::NO_ERROR_MESSAGE)
		]);

		$lang_args = [
			'api_code' => $this->getRandomString('foo'),
		];

		$this->response = ResponseBuilder::success(null, $this->random_api_code, $lang_args);
		$expected_message = \Lang::get(BaseApiCodes::getCodeMessageKey($this->random_api_code), $lang_args);
		$j = $this->getResponseSuccessObject($this->random_api_code, null, $expected_message);

		$this->assertNull($j->data);
	}


	/**
	 * Tests successWithCode() with custom API code and custom message
	 *
	 * @return void
	 */
	public function testSuccessWithCode_ApiCode_CustomMessageLang()
	{
		// for simplicity let's reuse existing message that is using placeholder
		\Config::set(ResponseBuilder::CONF_KEY_MAP, [
			$this->random_api_code => BaseApiCodes::getCodeMessageKey(BaseApiCodes::NO_ERROR_MESSAGE)
		]);

		$lang_args = [
			'api_code' => $this->getRandomString('foo'),
		];

		$this->response = ResponseBuilder::successWithCode($this->random_api_code, $lang_args);
		$expected_message = \Lang::get(BaseApiCodes::getCodeMessageKey($this->random_api_code), $lang_args);
		$j = $this->getResponseSuccessObject($this->random_api_code, null, $expected_message);

		$this->assertNull($j->data);
	}

	/**
	 * Checks success() with valid payload and HTTP code
	 *
	 * @return void
	 */
	public function testSuccess_DataAndHttpCode()
	{
		$payloads = [
			null,
			[$this->getRandomString() => $this->getRandomString()],
		];
		$http_codes = [HttpResponse::HTTP_OK       => null,
		               HttpResponse::HTTP_ACCEPTED => HttpResponse::HTTP_ACCEPTED,
		               HttpResponse::HTTP_OK       => HttpResponse::HTTP_OK];

		/** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();

		foreach ($payloads as $payload) {
			foreach ($http_codes as $http_code_expect => $http_code_send) {
				$this->response = ResponseBuilder::success($payload, null, [], $http_code_send);

				$j = $this->getResponseSuccessObject($api_codes_class_name::OK, $http_code_expect);

				if ($payload !== null) {
					$payload = (object)$payload;
				}
				$this->assertEquals($payload, $j->data);
			}
		}
	}

	/**
	 * @return void
	 *
	 * Tests successWithHttpCode()
	 */
	public function testSuccessHttpCode()
	{
		$http_codes = [HttpResponse::HTTP_ACCEPTED,
		               HttpResponse::HTTP_OK];
		foreach ($http_codes as $http_code) {
			$this->response = ResponseBuilder::successWithHttpCode($http_code);
			$j = $this->getResponseSuccessObject(0, $http_code);
			$this->assertNull($j->data);
		}
	}


	//----[ success ]-------------------------------------------

	/**
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccess_ApiCodeMustBeInt()
	{
		ResponseBuilder::success(null, 'foo');
	}

	/**
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccess_HttpCodeNull()
	{
		ResponseBuilder::successWithHttpCode(null);
	}

	/**
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessWithInvalidHttpCode()
	{
		ResponseBuilder::successWithHttpCode('invalid');
	}

	/**
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessWithTooBigHttpCode()
	{
		ResponseBuilder::successWithHttpCode(666);
	}

	/**
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessWithTooLowHttpCode()
	{
		ResponseBuilder::successWithHttpCode(0);
	}

	/**
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildSuccessResponse_InvalidReturnCode()
	{
		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'buildSuccessResponse');
		$method->invokeArgs($obj, [null,
		                           'string-is-invalid-code']);
	}

}
