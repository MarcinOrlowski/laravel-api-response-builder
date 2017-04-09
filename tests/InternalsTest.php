<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

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
class InternalsTest extends TestCase
{
	/**
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_WrongMessage()
	{
		/** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();

		$message_or_api_code = [];    // invalid

		$this->callMakeMethod(true, $api_codes_class_name::OK, $message_or_api_code);
	}

	/**
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_CustomMessageAndWrongCode()
	{
		$api_code = [];    // invalid
		/** @noinspection PhpParamsInspection */
		$this->callMakeMethod(true, $api_code, 'message');
	}

	/**
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_CustomMessageAndCodeOutOfRange()
	{
		$api_code = $this->max_allowed_code + 1;    // invalid
		$this->callMakeMethod(true, $api_code, 'message');
	}


	/**
	 * Validates make() handling invalid type of encoding_options
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_InvalidEncodingOptions()
	{
		\Config::set(ResponseBuilder::CONF_KEY_ENCODING_OPTIONS, []);
		$this->callMakeMethod(true, BaseApiCodes::OK, BaseApiCodes::OK);
	}

	/**
	 * Tests if dist's config detaults matches ResponseBuilder::DEFAULT_ENODING_OPTIONS
	 *
	 * @return void
	 */
	public function testDefaultEncodingOptionValue()
	{
		$config_defaults = \Config::get(ResponseBuilder::CONF_KEY_ENCODING_OPTIONS);
		$this->assertEquals($config_defaults, ResponseBuilder::DEFAULT_ENCODING_OPTIONS);
	}

	/**
	 * Tests fallback to default encoding_options
	 *
	 * @return void
	 */
	public function testMake_DefaultEncodingOptions()
	{
		// source data
		$test_string = 'ąćę';
//		$test_string_escaped = $this->escape8($test_string);
		$data = ['test' => $test_string];

		// fallback defaults in action
		\Config::offsetUnset('encoding_options');
		$resp = $this->callMakeMethod(true, BaseApiCodes::OK, BaseApiCodes::OK, $data);

		$matches = [];
		$this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $resp->getContent(), $matches));
		$result_defaults = $matches[1];


		// check if it returns the same when defaults enforced explicitly
		$resp = $this->callMakeMethod(true, BaseApiCodes::OK, BaseApiCodes::OK, $data, null, ResponseBuilder::DEFAULT_ENCODING_OPTIONS);

		$matches = [];
		$this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $resp->getContent(), $matches));
		$result_defaults_enforced = $matches[1];

		$this->assertEquals($result_defaults, $result_defaults_enforced);
	}

	/**
	 * Checks encoding_options influences result JSON data
	 *
	 * @return void
	 */
	public function testMake_ValidateEncodingOptionsPreventsEscaping()
	{
		$test_string = 'ąćę';
		$test_string_escaped = $this->escape8($test_string);

		// source data
		$data = ['test' => $test_string];

		// check if it returns escaped
		\Config::set(ResponseBuilder::CONF_KEY_ENCODING_OPTIONS, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
		$resp = $this->callMakeMethod(true, BaseApiCodes::OK, BaseApiCodes::OK, $data);

		$matches = [];
		$this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $resp->getContent(), $matches));
		$result_escaped = $matches[1];
		$this->assertEquals($test_string_escaped, $result_escaped);

		// check if it returns unescaped
		\Config::set(ResponseBuilder::CONF_KEY_ENCODING_OPTIONS, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
		$resp = $this->callMakeMethod(true, BaseApiCodes::OK, BaseApiCodes::OK, $data);

		$matches = [];
		$this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $resp->getContent(), $matches));
		$result_unescaped = $matches[1];
		$this->assertEquals($test_string, $result_unescaped);

		// this one is just in case...
		$this->assertNotEquals($result_escaped, $result_unescaped);
	}

	/**
	 * Checks make() handling invalid type of api_code argument
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_ApiCodeNotIntNorString()
	{
		$this->callMakeMethod(true, BaseApiCodes::OK, []);
	}


	/**
	 * Validates handling of wrong data type by getClassesMapping()
	 *
	 * @return void
	 *
	 * @expectedException \RuntimeException
	 */
	public function testGetClassesMapping_WrongType()
	{
		\Config::set(ResponseBuilder::CONF_KEY_CLASSES, false);

		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'getClassesMapping');
		$method->invokeArgs($obj, []);
	}


	/**
	 * Tests is custom response key mappings and defaults fallback work
	 *
	 * @return void
	 */
	public function testCustomResponseMapping()
	{
		\Config::set(ResponseBuilder::CONF_KEY_RESPONSE_KEY_MAP, [
				ResponseBuilder::KEY_SUCCESS => $this->getRandomString(),
			]
		);

		$this->response = ResponseBuilder::success();
		$j = $this->getResponseSuccessObject(BaseApiCodes::OK);
	}


	/**
	 * Tests is custom response key mappings and defaults fallback work
	 *
	 * @expectedException \RuntimeException
	 *
	 * @return void
	 */
	public function testGetResponseKey_UnknownKey()
	{
		BaseApiCodes::getResponseKey($this->getRandomString());
	}

	/**
	 * Tests validation of configuration validation of response key map
	 *
	 * @expectedException \RuntimeException
	 *
	 * @return void
	 */
	public function testResponseKeyMapping_InvalidMap()
	{
		\Config::set(ResponseBuilder::CONF_KEY_RESPONSE_KEY_MAP, 'invalid');
		BaseApiCodes::getResponseKey(ResponseBuilder::KEY_SUCCESS);
	}


	/**
	 * Tests validation of configuration validation of response key map
	 *
	 * @expectedException \RuntimeException
	 *
	 * @return void
	 */
	public function testResponseKeyMapping_InvalidMappingValue()
	{
		\Config::set(ResponseBuilder::CONF_KEY_RESPONSE_KEY_MAP, [
				ResponseBuilder::KEY_SUCCESS => false,
			]
		);

		BaseApiCodes::getResponseKey(ResponseBuilder::KEY_SUCCESS);
	}

}
