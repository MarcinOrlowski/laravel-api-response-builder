<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

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
class MakeTest extends Base\ResponseBuilderTestCaseBase
{
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_WrongMessage() {
		/** @var \MarcinOrlowski\ResponseBuilder\ErrorCode $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();

		$message_or_api_code = [];    // invalid

		$this->callMakeMethod($api_codes_class_name::OK, $message_or_api_code);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_CustomMessageAndWrongCode() {
		$api_code = [];    // invalid
		$this->callMakeMethod($api_code, 'message');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_CustomMessageAndCodeOutOfRange() {
		$api_code = $this->max_allowed_code + 1;    // invalid
		$this->callMakeMethod($api_code, 'message');
	}

}