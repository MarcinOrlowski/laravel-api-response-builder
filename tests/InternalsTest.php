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

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class InternalsTest extends TestCase
{
	/**
	 * @var HttpResponse
	 */
	protected $response;

	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * @return void
	 */
	public function testMake_WrongMessage(): void
	{
		$this->expectException(\InvalidArgumentException::class);

		/** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();

		$message_or_api_code = [];    // invalid

		/** @noinspection PhpUnhandledExceptionInspection */
		/** @noinspection PhpParamsInspection */
		$this->callMakeMethod(true, $api_codes_class_name::OK(), $message_or_api_code);
	}

	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * @return void
	 */
	public function testMake_CustomMessageAndCodeOutOfRange(): void
	{
		$this->expectException(\InvalidArgumentException::class);

		$api_code = $this->max_allowed_code + 1;    // invalid
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callMakeMethod(true, $api_code, 'message');
	}


	/**
	 * Validates make() handling invalid type of encoding_options
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * @return void
	 */
	public function testMake_InvalidEncodingOptions(): void
	{
		$this->expectException(\InvalidArgumentException::class);

		\Config::set(ResponseBuilder::CONF_KEY_ENCODING_OPTIONS, []);
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK());
	}

	/**
	 * Tests if dist's config detaults matches ResponseBuilder::DEFAULT_ENODING_OPTIONS
	 *
	 * @return void
	 */
	public function testDefaultEncodingOptionValue(): void
	{
		$config_defaults = \Config::get(ResponseBuilder::CONF_KEY_ENCODING_OPTIONS);
		$this->assertEquals($config_defaults, ResponseBuilder::DEFAULT_ENCODING_OPTIONS);
	}

	/**
	 * Tests fallback to default encoding_options
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * @return void
	 */
	public function testMake_DefaultEncodingOptions(): void
	{
		// source data
		$test_string = 'ąćę';
//		$test_string_escaped = $this->escape8($test_string);
		$data = ['test' => $test_string];

		// fallback defaults in action
		\Config::offsetUnset('encoding_options');
		/** @noinspection PhpUnhandledExceptionInspection */
		$resp = $this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK(), $data);

		$matches = [];
		$this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $resp->getContent(), $matches));
		$result_defaults = $matches[1];


		// check if it returns the same when defaults enforced explicitly
		/** @noinspection PhpUnhandledExceptionInspection */
		$resp = $this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK(), $data,
			null, ResponseBuilder::DEFAULT_ENCODING_OPTIONS);

		$matches = [];
		$this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $resp->getContent(), $matches));
		$result_defaults_enforced = $matches[1];

		$this->assertEquals($result_defaults, $result_defaults_enforced);
	}

	/**
	 * Checks encoding_options influences result JSON data
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * @return void
	 */
	public function testMake_ValidateEncodingOptionsPreventsEscaping(): void
	{
		$test_string = 'ąćę';
		$test_string_escaped = $this->escape8($test_string);

		// source data
		$data = ['test' => $test_string];

		// check if it returns escaped
		\Config::set(ResponseBuilder::CONF_KEY_ENCODING_OPTIONS, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
		/** @noinspection PhpUnhandledExceptionInspection */
		$resp = $this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK(), $data);

		$matches = [];
		$this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $resp->getContent(), $matches));
		$result_escaped = $matches[1];
		$this->assertEquals($test_string_escaped, $result_escaped);

		// check if it returns unescaped
		\Config::set(ResponseBuilder::CONF_KEY_ENCODING_OPTIONS,
			JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
		/** @noinspection PhpUnhandledExceptionInspection */
		$resp = $this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK(), $data);

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
	 * @throws \ReflectionException
	 */
	public function testMake_ApiCodeNotIntNorString(): void
	{
		$this->expectException(\InvalidArgumentException::class);

		/** @noinspection PhpUnhandledExceptionInspection */
		/** @noinspection PhpParamsInspection */
		$this->callMakeMethod(true, BaseApiCodes::OK(), []);
	}


	/**
	 * Validates handling of wrong data type by getClassesMapping()
	 *
	 * @return void
	 *
	 * @throws \ReflectionException
	 */
	public function testGetClassesMapping_WrongType(): void
	{
		$this->expectException(\RuntimeException::class);

		\Config::set(ResponseBuilder::CONF_KEY_CLASSES, false);

		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod($obj, 'getClassesMapping');
		$method->invokeArgs($obj, []);
	}


	/**
	 * Tests is custom response key mappings and defaults fallback work
	 *
	 * @return void
	 */
	public function testCustomResponseMapping(): void
	{
		\Config::set(ResponseBuilder::CONF_KEY_RESPONSE_KEY_MAP, [
				ResponseBuilder::KEY_SUCCESS => $this->getRandomString(),
			]
		);

		$this->response = ResponseBuilder::success();
		$this->getResponseSuccessObject(BaseApiCodes::OK());
	}


	/**
	 * Tests is custom response key mappings and defaults fallback work
	 *
	 * @return void
	 */
	public function testGetResponseKey_UnknownKey(): void
	{
		$this->expectException(\RuntimeException::class);

		BaseApiCodes::getResponseKey($this->getRandomString());
	}

	/**
	 * Tests validation of configuration validation of response key map
	 *
	 * @return void
	 */
	public function testResponseKeyMapping_InvalidMap(): void
	{
		$this->expectException(\RuntimeException::class);

		\Config::set(ResponseBuilder::CONF_KEY_RESPONSE_KEY_MAP, 'invalid');
		BaseApiCodes::getResponseKey(ResponseBuilder::KEY_SUCCESS);
	}


	/**
	 * Tests validation of configuration validation of response key map
	 *
	 * @return void
	 */
	public function testResponseKeyMapping_InvalidMappingValue(): void
	{
		$this->expectException(\RuntimeException::class);

		\Config::set(ResponseBuilder::CONF_KEY_RESPONSE_KEY_MAP, [
				ResponseBuilder::KEY_SUCCESS => false,
			]
		);

		BaseApiCodes::getResponseKey(ResponseBuilder::KEY_SUCCESS);
	}


	public function testGetCodeForInternalOffset_OffsetOutOfMaxBounds(): void
	{
		$obj = new BaseApiCodes();
		$max = $this->getProtectedConstant($obj, 'RESERVED_MAX_API_CODE_OFFSET');

		/** @noinspection PhpUnhandledExceptionInspection */
		$method = $this->getProtectedMethod($obj, 'getCodeForInternalOffset');

		$this->expectException(\InvalidArgumentException::class);
		$method->invokeArgs($obj, [$max + 1]);
	}

	public function testGetCodeForInternalOffset_OffsetOutOfMinBounds(): void
	{
		$obj = new BaseApiCodes();
		$min = $this->getProtectedConstant($obj, 'RESERVED_MIN_API_CODE_OFFSET');

		/** @noinspection PhpUnhandledExceptionInspection */
		$method = $this->getProtectedMethod($obj, 'getCodeForInternalOffset');

		$this->expectException(\InvalidArgumentException::class);
		$method->invokeArgs($obj, [$min - 1]);
	}

}
