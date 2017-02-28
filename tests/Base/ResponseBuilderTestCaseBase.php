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

use MarcinOrlowski\ResponseBuilder\ApiCodeBase;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Class ResponseBuilderTestCase
 */
abstract class ResponseBuilderTestCaseBase extends TestCaseBase
{
	/**
	 * @return ApiCodeBase
	 */
	public function getApiCodesObject()
	{
		return new ApiCodeBase();
	}

	/**
	 * @return string
	 */
	public function getApiCodesClassName()
	{
		return ApiCodeBase::class;
	}

	/** @var int */
	protected $min_allowed_code;

	/** @var int */
	protected $max_allowed_code;

	/** @var int */
	protected $random_api_code;

	/** @var array */
	protected $error_message_map = [];

	/**
	 * Localization key assigned to randomly choosen api_code
	 *
	 * @var string
	 */
	protected $random_api_code_message_key;

	/**
	 * Rendered value of final api code related message (with substitution)
	 *
	 * @var string
	 */
	protected $random_api_code_message;

	/**
	 * Sets up testing environment
	 *
	 * @return void
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
		$this->random_api_code = mt_rand($this->min_allowed_code, $this->max_allowed_code);

		// AND corresponding mapped message mapping
		$map = $this->getProtectedMember(ApiCodeBase::class, 'base_map');
		$idx = mt_rand(1, count($map));

		$this->random_api_code_message_key = $map[array_keys($map)[$idx-1]];
		$this->random_api_code_message = \Lang::get($this->random_api_code_message_key, [
			'api_code' => $this->random_api_code,
		]);
		$this->error_message_map = [
			$this->random_api_code => $this->random_api_code_message_key,
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
			\MarcinOrlowski\ResponseBuilder\Tests\Providers\ResponseBuilderServiceProvider::class,
		];
	}

	// -----------------------------------------------------------


	/**
	 * Checks if response object was returned with expected success HTTP
	 * code (200-299) indicating API method executed successfully
	 *
	 * NOTE: content of `data` node is NOT checked here!
	 *
	 * @param int|null    $expected_api_code  expected api code to be returned or @null for default
	 * @param int|null    $expected_http_code HTTP return code to check against or @null for default
	 * @param string|null $expected_message   Expected value of 'message' or @null for default message
	 *
	 * @return StdClass validated response object data (as object, not array)
	 *
	 */
	public function getResponseSuccessObject($expected_api_code = null,
	                                         $expected_http_code = null,
	                                         $expected_message = null)
	{
		if ($expected_api_code === null) {
			/** @var ApiCodeBase $api_codes */
			$api_codes = $this->getApiCodesClassName();
			$expected_api_code = $api_codes::OK;
		}

		if ($expected_http_code === null) {
			$expected_http_code = ResponseBuilder::DEFAULT_HTTP_CODE_OK;
		}

		if (($expected_http_code < 200) || ($expected_http_code > 299)) {
			$this->fail("TEST: Success HTTP code ($expected_http_code) in not in range: 200-299.");
		}

		if ($expected_message === null) {
			$key = ApiCodeBase::getCodeMessageKey($expected_api_code);
			if ($key === null) {
				$key = ApiCodeBase::getCodeMessageKey(ApiCodeBase::OK);
			}
			$expected_message = \Lang::get($key, ['api_code' => $expected_api_code]);
		}

		$j = $this->getResponseObjectRaw($expected_api_code, $expected_http_code, $expected_message);
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
			/** @var ApiCodeBase $api_codes_class_name */
			$api_codes_class_name = $this->getApiCodesClassName();
			$expected_api_code = $api_codes_class_name::NO_ERROR_MESSAGE;
		}

		if ($expected_http_code < HttpResponse::HTTP_BAD_REQUEST) {
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
		$this->assertEquals($expected_http_code, $actual,
			"Expected status code {$expected_http_code}, got {$actual}. Response: {$this->response->getContent()}");

		// get response as Json object
		$j = json_decode($this->response->getContent());
		$this->validateResponseStructure($j);

		$this->assertEquals($expected_api_code, $j->code);

		/** @var ApiCodeBase $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();
		$expected_message_string = ($expected_message === null)
			? \Lang::get($api_codes_class_name::getCodeMessageKey($expected_api_code), ['api_code' => $expected_api_code])
			: $expected_message;
		$this->assertEquals($expected_message_string, $j->message);

		return $j;
	}


	/**
	 * Validates if given $json_object contains all expected elements
	 *
	 * @param StdClass $json_object
	 *
	 * @return void
	 */
	protected function validateResponseStructure($json_object)
	{
		$this->assertTrue(is_object($json_object));

		$items = ['success',
		          'code',
		          'locale',
		          'message',
		          'data'];
		foreach ($items as $item) {
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
	 * Checks if Response's code matches our expectations. If not, shows ApiCodeBase::XXX constant name of expected and current values
	 *
	 * @param int      $expected_code ErrorCodes::XXX code expected
	 * @param StdClass $response_json response json object
	 *
	 * @return void
	 */
	public function assertResponseStatusCode($expected_code, $response_json)
	{
		$response_code = $response_json->code;

		if ($response_code !== $expected_code) {
			$msg = sprintf('Status code mismatch. Expected: %s, found %s. Message: "%s"',
				$this->resolveConstantFromCode($expected_code),
				$this->resolveConstantFromCode($response_code),
				$response_json->message);

			$this->fail($msg);
		}
	}

	//----------------------------

	/**
	 * Calls protected method make()
	 *
	 * @param boolean    $success             @true if response should indicate success, @false otherwise
	 * @param int        $api_code            API code to return
	 * @param string|int $message_or_api_code Resolvable Api code or message string
	 * @param array|null $data                Data to return
	 * @param array|null $headers             HTTP headers to include
	 * @param int|null   $encoding_options    see http://php.net/manual/en/function.json-encode.php
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function callMakeMethod($success, $api_code, $message_or_api_code, array $data = null, array $headers = null, $encoding_options = null)
	{
		if (!is_bool($success)) {
			$this->fail(sprintf("'success' must be boolean ('%s' given)", gettype($success)));
		}

		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'make');

		$http_code = null;
		$lang_args = null;

		return $method->invokeArgs($obj, [$success,
		                                             $api_code,
		                                             $message_or_api_code,
		                                             $data,
		                                             $http_code,
		                                             $lang_args,
		                                             $headers,
		                                             $encoding_options]);
	}


}
