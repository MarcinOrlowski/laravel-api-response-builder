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
use MarcinOrlowski\ResponseBuilder\ErrorCode;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class MakeTest extends ResponseBuilderTestCase
{
	//--[make]---------------------------------------------

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_WrongMessage() {
		$api_codes = $this->getApiCodesClassName();

		$message_or_api_code = [];    // invalid

		$this->validateMake($api_codes::OK, $message_or_api_code);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_CustomMessageAndWrongCode() {
		$api_code = [];    // invalid
		$this->validateMake($api_code, 'message');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMake_CustomMessageAndCodeOutOfRange() {
		$api_code = $this->max_allowed_code + 1;    // invalid
		$this->validateMake($api_code, 'message');
	}

	protected function validateMake($api_code, $message_or_api_code, array $headers=null) {
		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'make');

		$http_code = ResponseBuilder::DEFAULT_HTTP_CODE_OK;
		$lang_args = null;
		$data = null;

		$this->response = $method->invokeArgs($obj, [$api_code, $message_or_api_code,
		                                             $data, $http_code, $lang_args, $headers]);
	}
	//-----------------------------------------------

}