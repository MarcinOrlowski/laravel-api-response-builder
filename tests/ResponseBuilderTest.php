<?php

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
class ResponseBuilderTest extends TestCase {

	/**
	 * Checks if error codes range is set right
	 */
	public function testMinMaxCode() {
		$obj = new \App\ErrorCodes();

		$get_base_max_code = $this->getProtectedMethod(get_class($obj), 'getBaseMaxCode');
		$base_max = $get_base_max_code->invokeArgs($obj, []);

		$get_min_code = $this->getProtectedMethod(get_class($obj), 'getMinCode');
		$min = $get_min_code->invokeArgs($obj, []);

		$get_max_code = $this->getProtectedMethod(get_class($obj), 'getMaxCode');
		$max = $get_max_code->invokeArgs($obj, []);

		$this->assertTrue($min > $base_max);
		$this->assertTrue($max > $min);
	}

	/**
	 * Tests RB behaviour in case of missing _MIN_CODE
	 *
	 * @expectedException RuntimeException
	 */
	public function testBaseMinCodeMissing() {
		$obj = new DummyCodes();
		$get_min_code = $this->getProtectedMethod(get_class($obj), 'getMinCode');
		$get_min_code->invokeArgs($obj, []);
	}

	/**
	 * Tests RB behaviour in case of missing _MAX_CODE
	 *
	 * @expectedException RuntimeException
	 */
	public function testBaseMaxCodeMissing() {
		$obj = new DummyCodes();
		$get_max_code = $this->getProtectedMethod(get_class($obj), 'getMaxCode');
		$get_max_code->invokeArgs($obj, []);
	}

	/**
	 * Checks if all error codes defined in ErrorCodes class contain mapping entry
	 */
	public function testIfAllCodesGotMapping() {
		$codes = \App\ErrorCodes::getErrorCodeConstants();
		foreach( $codes as $name => $val ) {
			$this->assertNotNull(\App\ErrorCodes::getMapping($val), "No mapping for {$name}");
		}
	}

	/**
	 * Checks if all error codes are in allowed range
	 */
	public function testIfAllCodesAreInRange() {
		$codes = \App\ErrorCodes::getErrorCodeConstants();
		foreach( $codes as $name => $val ) {
			$this->assertTrue(\App\ErrorCodes::isCodeValid($val), "Value of {$name} is outside allowed range");
		}
	}

	/**
	 * Checks if all defined error code constants are unique (per value)
	 */
	public function testIfAllErrorValuesAreUnique() {
		$items = array_count_values(\App\ErrorCodes::getMap());
		foreach( $items as $code => $count ) {
			$this->assertFalse( ($count > 1), sprintf("Error code {$code} is not unique. Used {$count} times."));
		}
	}

	/**
	 * Checks if all codes are mapped to existing locale strings
	 *
	 * TODO: check translations too
	 */
	public function testIfAllCodesAreCorrectlyMapped() {
		$map = \App\ErrorCodes::getMap();
		foreach( $map as $code => $mapping ) {
			$str = \Lang::get($mapping);
			$this->assertNotEquals($mapping, $str,
				sprintf('No lang entry for: %s referenced by %s', $mapping, $this->resolveConstantFromCode($code))
			);
		}
	}


	//----[ success ]-------------------------------------------


	public function testSuccessGeneric() {
		// success();
		$this->response = \App\Helpers\ResponseBuilder::success();
		$this->assertResponseOk();

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateSuccessCommon($j);
		$this->assertNull($j->data);
	}

	public function testSuccessWithPayload() {
		// success(['payload'=>'foo']);
		$payload = ['payload' => 'foo'];
		$this->response = \App\Helpers\ResponseBuilder::success($payload);
		$this->assertResponseOk();

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateSuccessCommon($j);

		$this->assertNotNull($j->data);
		$this->assertTrue(is_object($j->data));
		$this->assertTrue(isset($j->data->payload));
		$this->assertEquals($j->data->payload, $payload['payload']);
	}

	public function testSuccessWithNoPayloadAndErrorCode() {
		// success(null, ErrorCode::CODE);
		$this->response = \App\Helpers\ResponseBuilder::success(null, \App\ErrorCodes::SERVICE_IN_MAINTENANCE);
		$this->assertResponseOk();

		$j = json_decode($this->response->getContent());
		$this->validateSuccessCommon($j, \App\ErrorCodes::SERVICE_IN_MAINTENANCE);
		$this->assertNull($j->data);
	}

	public function testSuccessWithPayloadAndErrorCode() {
		// success(['payload'=>'foo'], ErrorCode::CODE);
		$payload = ['payload' => 'foo'];
		$this->response = \App\Helpers\ResponseBuilder::success($payload, \App\ErrorCodes::SERVICE_IN_MAINTENANCE);
		$this->assertResponseOk();

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateSuccessCommon($j, \App\ErrorCodes::SERVICE_IN_MAINTENANCE);

		$this->assertNotNull($j->data);
		$this->assertTrue(is_object($j->data));
		$this->assertTrue(isset($j->data->payload));
		$this->assertEquals($j->data->payload, $payload['payload']);
	}

	public function testSuccessWithNoPayloadAndErrorCodeAndHttpCode() {
		// success(null, ErrorCode::CODE, HTTP_CODE);
		$http_code = 203;
		$error_code = \App\ErrorCodes::UNKNOWN_METHOD;
		$this->response = \App\Helpers\ResponseBuilder::success(null, $error_code, $http_code);
		$this->assertEquals($http_code, $this->response->getStatusCode());

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateSuccessCommon($j, $error_code);
	}

	public function testSuccessWithNoPayloadAndErrorCodeAndHttpCodeAndLangArgs() {
		// success(null, ErrorCode::CODE, HTTP_CODE, lang_args);
		$http_code = \App\Helpers\ResponseBuilder::DEFAULT_OK_HTTP_CODE;
		$error_code = \App\ErrorCodes::UNCAUGHT_EXCEPTION;
		$lang_args = ['message' => 'foo bar'];
		$this->response = \App\Helpers\ResponseBuilder::success(null, $error_code, $http_code, $lang_args);
		$this->assertEquals($http_code, $this->response->getStatusCode());

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateSuccessCommon($j, $error_code, $lang_args);
	}

	/**
	 * @expectedException \ErrorException
	 */
	public function testSuccessWithInvalidData() {
		\App\Helpers\ResponseBuilder::success('invalid');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessNonexistingErrorCodeWithMapping() {
		\App\Helpers\ResponseBuilder::success(null, -1);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessErrorCodeMustBeInt() {
		\App\Helpers\ResponseBuilder::success(null, 'foo');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildSuccessResponseWithInvalidErrorCode() {
		\App\Helpers\ResponseBuilder::successWithErrorCode('foo');
	}

	public function testSuccessWithHttpCode() {
		$http_code = 210;

		$this->response = \App\Helpers\ResponseBuilder::successWithHttpCode($http_code);
		$this->assertEquals($http_code, $this->response->getStatusCode());

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateSuccessCommon($j);
		$this->assertNull($j->data);
	}

	public function testSuccessWithHttpCodeFallBack() {
		$this->response = \App\Helpers\ResponseBuilder::successWithHttpCode(null);
		$this->assertResponseOk();

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateSuccessCommon($j);
		$this->assertNull($j->data);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessWithInvalidHttpCode() {
		\App\Helpers\ResponseBuilder::successWithHttpCode('invalid');
	}
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessWithTooBigHttpCode() {
		\App\Helpers\ResponseBuilder::successWithHttpCode(666);
	}
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessWithTooLowHttpCode() {
		\App\Helpers\ResponseBuilder::successWithHttpCode(0);
	}


	//----[ error ]-------------------------------------------

	public function testErrorGeneric() {
		// error(ErrorCode::CODE);
		$error_code = \App\ErrorCodes::UNKNOWN_ERROR;
		$this->response = \App\Helpers\ResponseBuilder::error($error_code);

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateErrorCommon($j, $error_code);
		$this->assertNull($j->data);
	}

	public function testErrorWithPayload() {
		// error(ErrorCode::CODE, ['payload'=>'foo']);
		$payload = ['payload' => 'foo'];
		$error_code = \App\ErrorCodes::UNKNOWN_ERROR;
		$this->response = \App\Helpers\ResponseBuilder::errorWithData($error_code, $payload);

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateErrorCommon($j, $error_code);

		$this->assertNotNull($j->data);
		$this->assertTrue(is_object($j->data));
		$this->assertTrue(isset($j->data->payload));
		$this->assertEquals($j->data->payload, $payload['payload']);
	}

	public function testErrorWithHttpCode() {
		// error(ErrorCode::CODE, HTTP CODE);
		$error_code = \App\ErrorCodes::UNKNOWN_ERROR;
		$http_code = \App\Helpers\ResponseBuilder::DEFAULT_ERROR_HTTP_CODE;
		$this->response = \App\Helpers\ResponseBuilder::errorWithHttpCode($error_code, $http_code);
		$this->assertEquals($http_code, $this->response->getStatusCode());

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateErrorCommon($j, $error_code);
		$this->assertNull($j->data);
	}

	public function testErrorWithMessage() {
		// error(ErrorCode::CODE, "foo", HTTP CODE);
		$msg = sha1(date('YmdHis'));

		$error_code = \App\ErrorCodes::UNKNOWN_ERROR;
		$http_code = \App\Helpers\ResponseBuilder::DEFAULT_ERROR_HTTP_CODE;
		$this->response = \App\Helpers\ResponseBuilder::errorWithMessage($error_code, $msg, $http_code);
		$this->assertEquals($http_code, $this->response->getStatusCode());

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateErrorCommon($j, $error_code, [], $msg);
		$this->assertNull($j->data);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponseWrongErrorCode() {
		$data = null;
		$http_code = 404;
		$error_code = 'wrong-error-code';
		$lang_args = null;

		$this->validateBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponseWrongData() {
		$data = 'string';
		$http_code = 404;
		$error_code = \App\ErrorCodes::UNKNOWN_ERROR;
		$lang_args = null;

		$this->validateBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponseWrongHttpCode() {
		$data = null;
		$http_code = 'string-is-invalid';
		$error_code = \App\ErrorCodes::UNKNOWN_ERROR;
		$lang_args = null;

		$this->validateBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	public function testBuildErrorResponseWithNullHttpCode() {
		$data = null;
		$http_code = null;
		$error_code = \App\ErrorCodes::UNKNOWN_ERROR;
		$lang_args = null;

		$this->validateBuildErrorResponse($data, $error_code, $http_code, $lang_args);

		$http_code = \App\Helpers\ResponseBuilder::DEFAULT_ERROR_HTTP_CODE;
		$this->assertEquals($http_code, $this->response->getStatusCode());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponseWithTooLowHttpCode() {
		$data = null;
		$http_code = 0;
		$error_code = \App\ErrorCodes::UNKNOWN_ERROR;
		$lang_args = null;

		$this->validateBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBuildErrorResponseWithWrongLangArgs() {
		$data = null;
		$http_code = 404;
		$error_code = \App\ErrorCodes::UNKNOWN_ERROR;
		$lang_args = 'string-is-invalid';

		$this->validateBuildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	protected function validateBuildErrorResponse($data, $error_code, $http_code, $lang_args) {
		$obj = new \App\Helpers\ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'buildErrorResponse');

		$this->response = $method->invokeArgs($obj, [$data, $error_code, $http_code, $lang_args]);
	}

	//--[make]---------------------------------------------

	public function testMakeWithMissingMapping() {
		$obj = new \App\ErrorCodes();
		$get_min_code = $this->getProtectedMethod(get_class($obj), 'getMinCode');
		$min = $get_min_code->invokeArgs($obj, []);

		$get_max_code = $this->getProtectedMethod(get_class($obj), 'getMaxCode');
		$max = $get_max_code->invokeArgs($obj, []);

		$map = \App\ErrorCodes::getMap();
		krsort($map);
		reset($map);

		$message_or_error_code = null;
		for($i=$min; $i<$max; $i++) {
			if( array_key_exists($i, $map) === false ) {
				$message_or_error_code = $i;
				break;
			}
		}

		if( $message_or_error_code === null ) {
			$this->fail("Failed to find unused error code value (within declared range) to perform this test");
		}

		$this->validateMake($message_or_error_code);

		$json_object = json_decode($this->response->getContent());
		$this->assertTrue(is_object($json_object));
		$this->assertEquals(\Lang::get(\App\ErrorCodes::getMapping(\App\ErrorCodes::NO_ERROR_DESCRIPTION),
			['error_code' => $message_or_error_code]), $json_object->message);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMakeWithWrongMessage() {
		$message_or_error_code = [];    // invalid

		$this->validateMake($message_or_error_code);
	}

	public function testMakeWithHeadersNull() {
		$this->validateMake(\App\ErrorCodes::UNKNOWN_ERROR, null);
	}

	protected function validateMake($message_or_error_code, $headers=[]) {
		$obj = new \App\Helpers\ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'make');

		$status = \App\Helpers\ResponseBuilder::STATUS_OK;
		$error_code = \App\ErrorCodes::OK;
		$data = null;

		$http_code = \App\Helpers\ResponseBuilder::DEFAULT_OK_HTTP_CODE;
		$lang_args = null;

		$this->response = $method->invokeArgs($obj, [$status, $error_code, $message_or_error_code, $data, $http_code, $lang_args, $headers]);
	}
	//-----------------------------------------------

	protected function validateSuccessCommon($json_object, $error_code=\App\ErrorCodes::OK, $lang_args=[]) {
		$this->validateResponseBuilderCommonStructure($json_object);
		$this->assertEquals($error_code, $json_object->code);
		$expected = \App\Helpers\ResponseBuilder::STATUS_OK;
		$this->assertEquals($expected, $json_object->status, "Status string is not '{$expected}' as expected");
		$this->assertEquals(\Lang::get(\App\ErrorCodes::getMapping($json_object->code), $lang_args), $json_object->message);
	}

	protected function validateErrorCommon($json_object, $error_code, $lang_args=[], $message=null) {
		$this->validateResponseBuilderCommonStructure($json_object);
		$this->assertEquals($error_code, $json_object->code);
		$expected = \App\Helpers\ResponseBuilder::STATUS_ERROR;
		$this->assertEquals($expected, $json_object->status, "Status string is not '{$expected}' as expected");
		if( is_null($message) ) {
			$this->assertEquals(\Lang::get(\App\ErrorCodes::getMapping($json_object->code), $lang_args), $json_object->message);
		} else {
			$this->assertEquals($message, $json_object->message);
		}
	}

	protected function validateResponseBuilderCommonStructure($json_object) {
		$this->assertTrue(is_object($json_object));
		$this->assertObjectHasAttribute('status', $json_object, "No 'status' in response");
		$this->assertObjectHasAttribute('code', $json_object, "No 'code' in response");
		$this->assertObjectHasAttribute('locale', $json_object, "No 'locale' in response");
		$this->assertEquals(\App::getLocale(), $json_object->locale, "Response locale mismatch");
		$this->assertObjectHasAttribute('message', $json_object, "No 'message' in response");
		$this->assertTrue($json_object->message != '', "Message cannot be empty string");

		$this->assertObjectHasAttribute('data', $json_object, "No 'data' in response");
	}

}


///**
// * Class DummyCodes to test misconfigured ErrorCodes class cases
// */
//
///** @noinspection PhpMultipleClassesDeclarationsInOneFile */
//class DummyCodes extends \App\ErrorCodesBase {}