<?php

namespace MarcinOrlowski\ResponseBuilder\Tests\Base;

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

/**
 * Class ResponseBuilderTestCase
 */
abstract class ResponseBuilderTestCaseBase extends TestCaseBase
{
	/**
	 * @return ErrorCode
	 */
	public function getApiCodesObject()
	{
		return new ErrorCode();
	}

	/**
	 * @return string
	 */
	public function getApiCodesClassName()
	{
		return '\MarcinOrlowski\ResponseBuilder\ErrorCode';
	}

	/** @var int */
	protected $min_allowed_code;

	/** @var int */
	protected $max_allowed_code;

	/** @var int */
	protected $random_error_code;

	/** @var array */
	protected $error_message_map = [];


	/**
	 * Sets up testing environment
	 */
	public function setUp()
	{
		parent::setUp();

		// Obtain configuration params
		$obj = $this->getApiCodesObject();
		$method = $this->getProtectedMethod(get_class($obj), 'getMinCode');
		$this->min_allowed_code = $method->invokeArgs($obj, []);

		$method = $this->getProtectedMethod(get_class($obj), 'getMaxCode');
		$this->max_allowed_code = $method->invokeArgs($obj, []);

		// generate random api_code
		/** @noinspection RandomApiMigrationInspection */
		$this->random_error_code = mt_rand($this->min_allowed_code, $this->max_allowed_code);

		// AND corresponding mapped message string
		$this->error_message_map = [
			$this->random_error_code => $this->getRandomString('setup_msg'),
		];
		\Config::set('response_builder.map', $this->error_message_map);
	}


	/**
	 * Load service providers we need during the tests
	 *
	 * @param \Illuminate\Foundation\Application $app
	 *
	 * @return array
	 */
	protected function getPackageProviders($app)
	{
		return [
			'\MarcinOrlowski\ResponseBuilder\TestResponseBuilderServiceProvider',
		];
	}

	// -----------------------------------------------------------


	/**
	 * Checks if response object was returned with expected success HTTP
	 * code (200-299) indicating API method executed successfully
	 *
	 * NOTE: content of `data` node is NOT checked here!
	 *
	 * @param int|null $expected_code expected api code to be returned
	 * @param int      $http_code     HTTP return code to check against
	 *
	 * @return StdClass validated response object data (as object, not array)
	 *
	 */
	public function getResponseSuccessObject($expected_code = null,
	                                         $http_code = ResponseBuilder::DEFAULT_HTTP_CODE_OK)
	{
		if ($expected_code === null) {
			/** @var ErrorCode $api_codes */
			$api_codes = $this->getApiCodesClassName();
			$expected_code = $api_codes::OK;
		}

		if (($http_code < 200) || ($http_code > 299)) {
			$this->fail("TEST: Success HTTP code ($http_code) in not in range: 200-299.");
		}

		$j = $this->getResponseObjectRaw($expected_code, $http_code);
		$this->assertEquals(true, $j->success);

		return $j;
	}


	/**
	 * Retrieves and validates response as expected from errorXXX() methods
	 *
	 * @param int|null    $expected_api_code  API code expected in response's 'code' field
	 * @param int         $expected_http_code Expected HTTP code
	 * @param string|null $message            Expected return message or @null if we automatically mapped message fits
	 *
	 * @return StdClass response object built from JSON
	 */
	public function getResponseErrorObject($expected_api_code = null,
	                                       $expected_http_code = ResponseBuilder::DEFAULT_HTTP_CODE_ERROR,
	                                       $message = null)
	{
		if ($expected_api_code === null) {
			/** @var ErrorCode $api_codes_class_name */
			$api_codes_class_name = $this->getApiCodesClassName();
			$expected_api_code = $api_codes_class_name::NO_ERROR_MESSAGE;
		}

		if ($expected_http_code < HttpResponse::HTTP_BAD_REQUEST)  {
			$this->fail(sprintf('TEST: Error HTTP code (%d) cannot be below %d', $expected_http_code, HttpResponse::HTTP_BAD_REQUEST));
		}

		$j = $this->getResponseObjectRaw($expected_api_code, $expected_http_code, $message);
		$this->assertEquals(false, $j->success);

		return $j;
	}


	/**
	 * @param int         $expected_api_code
	 * @param int         $expected_http_code
	 * @param string|null $expected_message
	 *
	 * @return mixed
	 */
	private function getResponseObjectRaw($expected_api_code, $expected_http_code, $expected_message = null)
	{
		$actual = $this->response->getStatusCode();
		$this->assertEquals($expected_http_code, $actual, "Expected status code {$expected_http_code}, got {$actual}. Response: {$this->response->getContent()}");

		// get response as Json object
		$j = json_decode($this->response->getContent());
		$this->validateResponseStructure($j);

		/** @var ErrorCode $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();
		$this->assertEquals($expected_api_code, $j->code);
		$expected_message_string = ($expected_message === null) ? \Lang::get($api_codes_class_name::getMapping($expected_api_code)) : $expected_message;
		$this->assertEquals($expected_message_string, $j->message);

		return $j;
	}



	/**
	 * Validates if given $json_object contains all expected elements
	 *
	 * @param $json_object
	 */
	protected function validateResponseStructure($json_object) {
		$this->assertTrue(is_object($json_object));

		$items = ['success',
		          'code',
		          'locale',
		          'message',
		          'data'];
		foreach($items as $item) {
			$this->assertObjectHasAttribute($item, $json_object, "No '{$item}' element in response structure found");
		}

		$this->assertTrue(is_bool($json_object->success));
		$this->assertTrue(is_int($json_object->code));
		$this->assertTrue(is_string($json_object->locale));
		/** @noinspection UnNecessaryDoubleQuotesInspection */
		$this->assertNotEquals(trim($json_object->locale), '', "'message' cannot be empty string");
		$this->assertTrue(is_string($json_object->message));
		/** @noinspection UnNecessaryDoubleQuotesInspection */
		$this->assertNotEquals(trim($json_object->message), '', "'locale' cannot be empty string");
		$this->assertTrue(($json_object->data === null) || is_object($json_object->data),
			"Response 'data' must be either object or null");
	}


// ---------------------------------------------------------


	/**
	 * Checks if response object was returned with expected success HTTP
	 * code (200-299) indicating API method executed successfully
	 *
	 * @param int $http_code HTTP return code to check against
	 *
	 * @deprecated
	 */
	public function assertResponseOk($http_code = HttpResponse::HTTP_OK) {
		if (($http_code < 200) || ($http_code > 299)) {
			$this->fail("TEST: Success HTTP code ($http_code) in not in range: 200-299.");
		}

		$actual = $this->response->getStatusCode();

		$this->assertEquals($http_code, $actual, "Expected status code {$http_code}, got {$actual}. Response: {$this->response->getContent()}");
	}


	/**
	 * Checks if Response's code matches our expectations. If not, shows ErrorCode::XXX constant name of expected and current values
	 *
	 * @param int      $expected_code ErrorCodes::XXX code expected
	 * @param StdClass $response_json response json object
	 */
	public function assertResponseStatusCode($expected_code, $response_json) {
		$response_code = $response_json->code;

		if( $response_code !== $expected_code ) {
			$msg = sprintf('Status code mismatch. Expected: %s, found %s. Message: "%s"',
				$this->resolveConstantFromCode($expected_code),
				$this->resolveConstantFromCode($response_code),
				$response_json->message);

			$this->fail($msg);
		}
	}

	//----------------------------

	/**
	 * @param            $api_code
	 * @param            $message_or_api_code
	 * @param array|null $headers
	 */
	protected function callMakeMethod($api_code, $message_or_api_code, array $headers=null) {
		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'make');

		$http_code = ResponseBuilder::DEFAULT_HTTP_CODE_OK;
		$lang_args = null;
		$data = null;

		$this->response = $method->invokeArgs($obj, [$api_code, $message_or_api_code,
		                                             $data, $http_code, $lang_args, $headers]);
	}


}
