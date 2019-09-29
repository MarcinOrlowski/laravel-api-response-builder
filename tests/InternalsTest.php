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
	 * @noinspection PhpDocMissingThrowsInspection
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
	 */
	public function testMake_InvalidEncodingOptions(): void
	{
		$this->expectException(\InvalidArgumentException::class);

		/** @noinspection PhpUndefinedClassInspection */
		\Config::set(ResponseBuilder::CONF_KEY_ENCODING_OPTIONS, []);
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK());
	}

	/**
	 * Tests if dist's config detaults matches ResponseBuilder::DEFAULT_ENODING_OPTIONS
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
	 */
	public function testMake_DefaultEncodingOptions(): void
	{
		// source data
		$test_string = 'ąćę';
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
	 */
	public function testMake_ValidateEncodingOptionsPreventsEscaping(): void
	{
		$test_string = 'ąćę';
		$test_string_escaped = $this->escape8($test_string);

		// source data
		$data = ['test' => $test_string];

		// check if it returns escaped
		/** @noinspection PhpUndefinedClassInspection */
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
	 * Validates handling of incomplete class mapping configuration by getClassesMapping()
	 *
	 * @throws \ReflectionException
	 */
	public function testGetClassesMapping_WrongType(): void
	{
		\Config::set(ResponseBuilder::CONF_KEY_CLASSES, false);

		$this->expectException(\RuntimeException::class);

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callProtectedMethod(new ResponseBuilder(), 'getClassesMapping');
	}

	public function testGetClassesMapping_IncompleteMappingConfiguration(): void
	{
		\Config::set(ResponseBuilder::CONF_KEY_CLASSES, [
			self::class => [],
		]);

		$this->expectException(\RuntimeException::class);

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callProtectedMethod(new ResponseBuilder(), 'getClassesMapping');
	}

	/**
	 * Tests getCodeForInternalOffset() out of bounds handling
	 *
	 * @throws \ReflectionException
	 */
	public function testGetCodeForInternalOffset_OffsetOutOfMaxBounds(): void
	{
		$obj = new BaseApiCodes();
		$max = $this->getProtectedConstant($obj, 'RESERVED_MAX_API_CODE_OFFSET');

		$this->expectException(\InvalidArgumentException::class);
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callProtectedMethod($obj, 'getCodeForInternalOffset', [$max + 1]);
	}

	/**
	 * Tests getCodeForInternalOffset() out of bounds handling
	 *
	 * @throws \ReflectionException
	 */
	public function testGetCodeForInternalOffset_OffsetOutOfMinBounds(): void
	{
		$obj = new BaseApiCodes();
		$min = $this->getProtectedConstant($obj, 'RESERVED_MIN_API_CODE_OFFSET');

		$this->expectException(\InvalidArgumentException::class);
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callProtectedMethod($obj, 'getCodeForInternalOffset', [$min - 1]);
	}


	public function testGetCodeMessageKey_WithKeyOutOfBounds(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		BaseApiCodes::getCodeMessageKey(BaseApiCodes::getMaxCode() + 1);
	}

	public function testGetApiCodeConstants(): void
	{
		$expected = [
			'RESERVED_MIN_API_CODE_OFFSET',
			'RESERVED_MAX_API_CODE_OFFSET',

			'OK_OFFSET',
			'NO_ERROR_MESSAGE_OFFSET',
			'EX_HTTP_NOT_FOUND_OFFSET',
			'EX_HTTP_SERVICE_UNAVAILABLE_OFFSET',
			'EX_HTTP_EXCEPTION_OFFSET',
			'EX_UNCAUGHT_EXCEPTION_OFFSET',
			'EX_AUTHENTICATION_EXCEPTION_OFFSET',
			'EX_VALIDATION_EXCEPTION_OFFSET',
		];
		/** @noinspection PhpUnhandledExceptionInspection */
		$consts = BaseApiCodes::getApiCodeConstants();

		foreach ($expected as $key) {
			$this->assertArrayHasKey($key, $consts);
		}
	}
}
