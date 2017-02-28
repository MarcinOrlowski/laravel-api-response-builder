<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;
use MarcinOrlowski\ResponseBuilder\ApiCodeBase;
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
class InternalsTest extends Base\ResponseBuilderTestCaseBase
{
	/**
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_WrongMessage()
	{
		/** @var \MarcinOrlowski\ResponseBuilder\ApiCodeBase $api_codes_class_name */
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
		\Config::set('encoding_options', []);
		$this->callMakeMethod(true, ApiCodeBase::OK, ApiCodeBase::OK);
	}

	/**
	 * Checks encoding_options influences result JSON data
	 *
	 * @return void
	 */
	public function testMake_ValidateEncodingOptionsPreventsEscaping()
	{
		$test_string = 'ąćę';
		$test_string_escaped = '';

		// escape UTF8 for further comparision
		$offset = 0;
		while ($offset >= 0) {
			$test_string_escaped .= sprintf('\u%04x', $this->ord8($test_string, $offset));
		}

		// source data
		$data = ['test' => $test_string];

		// check if it returns escaped
		\Config::set('encoding_options', JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
		$resp = $this->callMakeMethod(true, ApiCodeBase::OK, ApiCodeBase::OK, $data);

		$matches = [];
		$this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $resp->getContent(), $matches));
		$result_escaped = $matches[1];
		$this->assertEquals($test_string_escaped, $result_escaped);


		// check if it returns unescaped
		\Config::set('encoding_options', JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE);
		$resp = $this->callMakeMethod(true, ApiCodeBase::OK, ApiCodeBase::OK, $data);

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
		$this->callMakeMethod(true, ApiCodeBase::OK, []);
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
		\Config::set('response_builder.classes', false);

		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'getClassesMapping');
		$method->invokeArgs($obj, []);

	}
}
