<?php

use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use MarcinOrlowski\ResponseBuilder\ErrorCode;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class MakeTest extends ResponseBuilderTestCase
{
	//--[make]---------------------------------------------

//	public function testMakeWithMissingMapping() {
//		$min = $this->min_allowed_code;
//		$max = $this->max_allowed_code;
//
//		$map = ErrorCodes::getMap();
//		krsort($map);
//		reset($map);
//
//		$message_or_error_code = null;
//		for($i=$min; $i<$max; $i++) {
//			if( array_key_exists($i, $map) === false ) {
//				$message_or_error_code = $i;
//				break;
//			}
//		}
//
//		if( $message_or_error_code === null ) {
//			$this->fail("Failed to find unused error code value (within declared range) to perform this test");
//		}
//
//		$this->validateMake($message_or_error_code);
//
//		$json_object = json_decode($this->response->getContent());
//		$this->assertTrue(is_object($json_object));
//		$this->assertEquals(\Lang::get(ErrorCodes::getMapping(ErrorCodes::NO_ERROR_DESCRIPTION),
//			['error_code' => $message_or_error_code]), $json_object->message);
//	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMakeWithWrongMessage() {
		$message_or_error_code = [];    // invalid

		$this->validateMake($message_or_error_code);
	}

	protected function validateMake($message_or_error_code, $headers=[]) {
		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'make');

		$error_code = ErrorCode::OK;
		$data = null;

		$http_code = ResponseBuilder::DEFAULT_HTTP_CODE_OK;
		$lang_args = null;

		$this->response = $method->invokeArgs($obj, [$error_code, $message_or_error_code, $data, $http_code, $lang_args, $headers]);
	}
	//-----------------------------------------------

}