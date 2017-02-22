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
