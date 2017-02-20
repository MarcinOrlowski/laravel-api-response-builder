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

/**
 * Class ResponseBuilderTestCase
 */
abstract class ResponseBuilderTestCase extends TestCaseBase
{
	public function getApiCodesObject()
	{
		return new ErrorCode();
	}

	public function getApiCodesClassName()
	{
		return 'MarcinOrlowski\ResponseBuilder\ErrorCode';
	}


	public function setUp()
	{
		parent::setUp();

		Config::set('response_builder.min_code', 100);
		Config::set('response_builder.max_code', 399);

		$obj = $this->getApiCodesObject();
		$method = $this->getProtectedMethod(get_class($obj), 'getMinCode');
		$this->min_allowed_code = $method->invokeArgs($obj, []);

		$method = $this->getProtectedMethod(get_class($obj), 'getMaxCode');
		$this->max_allowed_code = $method->invokeArgs($obj, []);

		$this->random_error_code = mt_rand($this->min_allowed_code, $this->max_allowed_code);

		// AND corresponding mapped message string
		$this->error_message_map = [
			$this->random_error_code => $this->getRandomString('setup_msg'),
		];
		Config::set('response_builder.map', $this->error_message_map);
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
			\MarcinOrlowski\ResponseBuilder\TestResponseBuilderServiceProvider::class,
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
	 * @return validated response object data (as object, not array)
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


	public function getResponseErrorObject($expected_code = null,
	                                       $http_code = ResponseBuilder::DEFAULT_HTTP_CODE_ERROR,
	                                       $message = null)
	{
		if ($expected_code === null) {
			/** @var ErrorCode $api_codes */
			$api_codes = $this->getApiCodesClassName();
			$expected_code = $api_codes::NO_ERROR_MESSAGE;
		}

		if ($http_code < HttpResponse::HTTP_BAD_REQUEST)  {
			$this->fail(sprintf("TEST: Error HTTP code (%d) cannot be below %d.", $http_code, HttpResponse::HTTP_BAD_REQUEST));
		}

		$j = $this->getResponseObjectRaw($expected_code, $http_code, $message);
		$this->assertEquals(false, $j->success);

		return $j;
	}


	private function getResponseObjectRaw($expected_code, $http_code, $message = null)
	{
		$actual = $this->response->getStatusCode();
		$this->assertEquals($http_code, $actual, "Expected status code {$http_code}, got {$actual}. Response: {$this->response->getContent()}");

		// get response as Json object
		$j = json_decode($this->response->getContent());
		$this->validateResponseStructure($j);

		$api_codes = $this->getApiCodesClassName();
		$this->assertEquals($expected_code, $j->code);
		$expected_message = ($message === null) ? \Lang::get($api_codes::getMapping($expected_code)) : $message;
		$this->assertEquals($expected_message, $j->message);

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
		$this->assertTrue(trim($json_object->locale) != '', "'message' cannot be empty string");
		$this->assertTrue(is_string($json_object->message));
		$this->assertTrue(trim($json_object->message) != '', "'locale' cannot be empty string");
		$this->assertTrue(($json_object->data === null) || (is_object($json_object->data)),
			"Response 'data' must be either object or null");
	}



	/**
	 * Helper to let test protected/private methods
	 *
	 * Usage example:
	 * ----------------
	 *   $method = $this->getProtectedMethod('App\Foo', 'someMethod');
	 *   $obj = new \App\Foo();
	 *   $result = $method->invokeArgs($obj, ...);
	 *
	 * @param string $class_name name of the class method belongs to, i.e. "Bar". Can be namespaced i.e. "Foo\Bar" (no starting backslash)
	 * @param string $method_name method name to call
	 *
	 * @return \ReflectionMethod
	 */
	public function getProtectedMethod($class_name, $method_name) {
		$class = new \ReflectionClass($class_name);
		$method = $class->getMethod($method_name);
		$method->setAccessible(true);

		return $method;
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
	 * @param       $json_object
	 * @param int   $code
	 * @param array $lang_args
	 *
	 * @deprecated
	 */
	protected function validateSuccessCommon($json_object, $code=0, $lang_args=[]) {
		$api_codes = $this->getApiCodesClassName();

		$this->validateResponseStructure($json_object);
		$this->assertEquals($code, $json_object->code);
		$this->assertEquals(\Lang::get($api_codes::getMapping($json_object->code), $lang_args), $json_object->message);
	}



	/**
	 * Checks if Response's code matches our expectations. If not, shows ErrorCode::XXX constant name of expected and current values
	 *
	 * @param int $expected_code ErrorCodes::XXX code expected
	 * @param $response_json response json object
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

}
