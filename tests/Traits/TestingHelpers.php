<?php

namespace MarcinOrlowski\ResponseBuilder\Tests\Traits;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Unit test helper trait
 */
trait TestingHelpers
{

	/**
	 * @return string
	 */
	abstract public function getApiCodesClassName();


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
		$class_name = $this->getApiCodesClassName();
		$obj = new $class_name();

		$method = $this->getProtectedMethod(get_class($obj), 'getMinCode');
		$this->min_allowed_code = $method->invokeArgs($obj, []);

		$method = $this->getProtectedMethod(get_class($obj), 'getMaxCode');
		$this->max_allowed_code = $method->invokeArgs($obj, []);

		// generate random api_code
		/** @noinspection RandomApiMigrationInspection */
		$this->random_api_code = mt_rand($this->min_allowed_code, $this->max_allowed_code);

		// AND corresponding mapped message mapping
		$map = $this->getProtectedMember(\MarcinOrlowski\ResponseBuilder\BaseApiCodes::class, 'base_map');
		$idx = mt_rand(1, count($map));

		$this->random_api_code_message_key = $map[ array_keys($map)[ $idx - 1 ] ];
		$this->random_api_code_message = \Lang::get($this->random_api_code_message_key, [
			'api_code' => $this->random_api_code,
		]);
		$this->error_message_map = [
			$this->random_api_code => $this->random_api_code_message_key,
		];
		\Config::set(ResponseBuilder::CONF_KEY_MAP, $this->error_message_map);
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
			/** @var BaseApiCodes $api_codes */
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
			$key = \MarcinOrlowski\ResponseBuilder\BaseApiCodes::getCodeMessageKey($expected_api_code);
			if ($key === null) {
				$key = \MarcinOrlowski\ResponseBuilder\BaseApiCodes::getCodeMessageKey(\MarcinOrlowski\ResponseBuilder\BaseApiCodes::OK);
			}
			$expected_message = \Lang::get($key, ['api_code' => $expected_api_code]);
		}

		$j = $this->getResponseObjectRaw($expected_api_code, $expected_http_code, $expected_message);
		$this->assertEquals(true, $j->{BaseApiCodes::getResponseKey(ResponseBuilder::KEY_SUCCESS)});

		return $j;
	}


	/**
	 * Retrieves and validates response as expected from errorXXX() methods
	 *
	 * @param int|null    $expected_api_code  API code expected in response's 'code' field
	 * @param int         $expected_http_code Expected HTTP code
	 * @param string|null $message            Expected return message or @null if we automatically mapped message fits
	 * @param array       $extra_keys         array of additional keys expected in response structure
	 *
	 * @return StdClass response object built from JSON
	 */
	public function getResponseErrorObject($expected_api_code = null,
	                                       $expected_http_code = ResponseBuilder::DEFAULT_HTTP_CODE_ERROR,
	                                       $message = null,
	                                       $extra_keys = [])
	{
		if ($expected_api_code === null) {
			/** @var BaseApiCodes $api_codes_class_name */
			$api_codes_class_name = $this->getApiCodesClassName();
			$expected_api_code = $api_codes_class_name::NO_ERROR_MESSAGE;
		}

		if ($expected_http_code < HttpResponse::HTTP_BAD_REQUEST) {
			$this->fail(sprintf('TEST: Error HTTP code (%d) cannot be below %d', $expected_http_code, HttpResponse::HTTP_BAD_REQUEST));
		}

		$j = $this->getResponseObjectRaw($expected_api_code, $expected_http_code, $message, $extra_keys);
		$this->assertEquals(false, $j->success);

		return $j;
	}


	/**
	 * @param int         $expected_api_code  expected Api response code
	 * @param int         $expected_http_code expected HTTP code
	 * @param string|null $expected_message   expected message string or @null if default
	 * @param array       $extra_keys         array of additional keys expected in response structure
	 *
	 * @return mixed
	 */
	private function getResponseObjectRaw($expected_api_code, $expected_http_code, $expected_message = null, array $extra_keys = [])
	{
		$actual = $this->response->getStatusCode();
		$this->assertEquals($expected_http_code, $actual,
			"Expected status code {$expected_http_code}, got {$actual}. Response: {$this->response->getContent()}");

		// get response as Json object
		$j = json_decode($this->response->getContent());
		$this->validateResponseStructure($j, $extra_keys);

		$this->assertEquals($expected_api_code, $j->code);

		/** @var BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();
		$expected_message_string = ($expected_message === null)
			? \Lang::get($api_codes_class_name::getCodeMessageKey($expected_api_code), ['api_code' => $expected_api_code])
			: $expected_message;
		$this->assertEquals($expected_message_string, $j->message);

		return $j;
	}


	/**
	 * Use assertValidResponse() instead
	 *
	 * @param StdClass $json_object
	 * @param array    $extra_keys
	 *
	 * @return void
	 *
	 * @deprecated Use assertValidResponse() instead
	 */
	public function validateResponseStructure($json_object, array $extra_keys = [])
	{
		$this->assertValidResponse($json_object, $extra_keys);
	}

	/**
	 * Validates if given $json_object contains all expected elements
	 *
	 * @param StdClass $json_object JSON Object hodling Api response to validate
	 * @param array    $extra_keys  array of additional keys expected in response structure
	 *
	 * @return void
	 */
	public function assertValidResponse($json_object, array $extra_keys = [])
	{
		$this->assertTrue(is_object($json_object));

		$items_ref = [
			ResponseBuilder::KEY_SUCCESS,
			ResponseBuilder::KEY_CODE,
			ResponseBuilder::KEY_LOCALE,
			ResponseBuilder::KEY_MESSAGE,
			ResponseBuilder::KEY_DATA,
		];

		$items = [];
		foreach ($items_ref as $ref) {
			$items[ $ref ] = BaseApiCodes::getResponseKey($ref);
		}

		$items = array_merge_recursive($items, $extra_keys);
		foreach ($items as $ref => $item) {
			$this->assertObjectHasAttribute($item, $json_object, "No '{$item}' element in response structure found");
		}

		$this->assertTrue(is_bool($json_object->{$items[ ResponseBuilder::KEY_SUCCESS ]}));
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
	 * Checks if Response's code matches our expectations. If not, shows
	 * \MarcinOrlowski\ResponseBuilder\ApiCodeBase::XXX constant name of expected and current values
	 *
	 * @param int      $expected_code ApiCode::XXX code expected
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
	 * @param array|null $debug_data          optional data to be included in response JSON
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function callMakeMethod($success, $api_code, $message_or_api_code, array $data = null, array $headers = null,
	                                  $encoding_options = null, array $debug_data = null)
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
		                                  $encoding_options,
		                                  $debug_data]);
	}


	// -------------------------------


	/**
	 * Returns ErrorCode constant name referenced by its value
	 *
	 * @param int $api_code value to match constant name for
	 *
	 * @return int|null|string
	 */
	protected function resolveConstantFromCode($api_code)
	{
		/** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();
		/** @var array $const */
		$const = $api_codes_class_name::getApiCodeConstants();
		$name = null;
		foreach ($const as $const_name => $const_value) {
			if (is_int($const_value) && ($const_value === $api_code)) {
				$name = $const_name;
				break;
			}
		}

		return ($name === null) ? "??? ({$api_code})" : $name;
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
	 * @param string $class_name  method's class name to, i.e. "Bar". Can be namespaced i.e. "Foo\Bar" (no starting backslash)
	 * @param string $method_name method name to call
	 *
	 * @return \ReflectionMethod
	 */
	protected function getProtectedMethod($class_name, $method_name)
	{
		$class = new \ReflectionClass($class_name);
		$method = $class->getMethod($method_name);
		$method->setAccessible(true);

		return $method;
	}

	/**
	 * Returns value of otherwise non-public member of the class
	 *
	 * @param string $class_name  class name to get member from
	 * @param string $member_name member name
	 *
	 * @return mixed
	 */
	protected function getProtectedMember($class_name, $member_name)
	{
		$reflection = new \ReflectionClass($class_name);
		$property = $reflection->getProperty($member_name);
		$property->setAccessible(true);

		return $property->getValue($class_name);
	}

	/**
	 * Generates random string, with optional prefix
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	protected function getRandomString($prefix = null)
	{
		if ($prefix !== null) {
			$prefix = "{$prefix}_";
		}

		return $prefix . md5(uniqid(mt_rand(), true));
	}


	/**
	 * UTF8 aware version of ord()
	 *
	 * @param string $string UTF8 string to work on
	 * @param int    $offset start offset. Note, offset will be updated to properly skip multibyte chars!
	 *
	 * $text = "abcàêß€abc";
	 * $offset = 0;
	 * while ($offset >= 0) {
	 *    printf("%d: %d\n", $offset, ord8($text, $offset));
	 * }
	 *
	 * @return int code of the character
	 */
	protected function ord8($string, &$offset)
	{
		$code = ord(substr($string, $offset, 1));
		if ($code >= 128) {             //otherwise 0xxxxxxx
			if ($code < 224) {          //110xxxxx
				$bytesnumber = 2;
			} elseif ($code < 240) {    //1110xxxx
				$bytesnumber = 3;
			} elseif ($code < 248) {    //11110xxx
				$bytesnumber = 4;
			}

			$codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
			for ($i = 2; $i <= $bytesnumber; $i++) {
				$offset++;
				$code2 = ord(substr($string, $offset, 1)) - 128;        //10xxxxxx
				$codetemp = $codetemp * 64 + $code2;
			}
			$code = $codetemp;
		}
		$offset += 1;
		if ($offset >= strlen($string)) {
			$offset = -1;
		}

		return $code;
	}

	/**
	 * UTF8 escape of given string
	 *
	 * @param string $string UTF8 string to escape
	 *
	 * @return string
	 */
	protected function escape8($string)
	{
		$escaped = '';

		// escape UTF8 for further comparision
		$offset = 0;
		while ($offset >= 0) {
			$escaped .= sprintf('\u%04x', $this->ord8($string, $offset));
		}

		return $escaped;
	}

}