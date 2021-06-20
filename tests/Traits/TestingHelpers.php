<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Traits;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2021 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Builder;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Validator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Unit test helper trait
 */
trait TestingHelpers
{
    /**
     * @return string
     */
    abstract public function getApiCodesClassName(): string;

    /** @var int */
    protected $min_allowed_code;

    /** @var int */
    protected $max_allowed_code;

    /** @var int */
    protected $random_api_code;

    /** @var array */
    protected $error_message_map = [];

    /**
     * Localization key assigned to randomly chosen api_code
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

    // -----------------------------------------------------------------------------------------------------------

	/**
	 * Sets up testing environment
	 *
	 * @return void
	 *
	 * @throws \ReflectionException
	 */
    public function setUp(): void
    {
        parent::setUp();

        // Obtain configuration params
        $class_name = $this->getApiCodesClassName();

        $obj = new $class_name();
        $this->min_allowed_code = $this->callProtectedMethod($obj, 'getMinCode');
        $this->max_allowed_code = $this->callProtectedMethod($obj, 'getMaxCode');

        // generate random api_code
        /** @noinspection RandomApiMigrationInspection */
        $this->random_api_code = \mt_rand($this->min_allowed_code, $this->max_allowed_code);

        // AND corresponding mapped message mapping
        $map = $this->callProtectedMethod(new BaseApiCodes(), 'getBaseMap');
        $idx = \random_int(1, \count($map));
        $this->random_api_code_message_key = $map[ \array_keys($map)[ $idx - 1 ] ];
	    $this->random_api_code_message = $this->langGet($this->random_api_code_message_key, ['api_code' => $this->random_api_code,]);

	    $this->error_message_map = [
            $this->random_api_code => $this->random_api_code_message_key,
        ];
        \Config::set(RB::CONF_KEY_MAP, $this->error_message_map);
    }

	// -----------------------------------------------------------------------------------------------------------

	/**
	 * We wrap call to response's getContent() to handle case of `false` being return value.
	 *
	 * @param \Symfony\Component\HttpFoundation\Response $response
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException If response object is not of HttpResponse class.
	 * @throws \RuntimeException if there's no conent to be returned from the response.
	 */
	public function getResponseContent(HttpResponse $response): string
	{
		$content = $response->getContent();
		if ($content === false) {
			throw new \RuntimeException('Response does not contains any content.');
		}
		return $content;
	}

	// -----------------------------------------------------------------------------------------------------------

	/**
	 * As Lang::get() wrapper can also return whole translation array, not only single strings,
	 * this make static code analysers unhappy as its signature indicates it can return arrays too, which we do not want to happen,
	 * not handle separately after each invocation, so this wrapper deals with it for us.
	 *
	 * @param string     $key     String key as passed to Lang::get()
	 * @param array|null $replace Optional replacement array as passed to Lang::get()
	 *
	 * @return string
	 */
	public function langGet($key, array $replace = null): string
	{
		$replace = $replace ?? [];
		$result = \Lang::get($key, $replace);
		if (is_array($result)) {
			$result = implode('', $result);
		}
		return $result;
	}

	// -----------------------------------------------------------------------------------------------------------

	/**
	 * Checks if response object was returned with expected success HTTP
	 * code (200-299) indicating API method executed successfully
	 *
	 * NOTE: content of `data` node is NOT checked here!
	 *
	 * @param int|null    $expected_api_code_offset expected api code offset or @null for default value
	 * @param int|null    $expected_http_code       HTTP return code to check against or @null for default
	 * @param string|null $expected_message         Expected value of 'message' or @null for default message
	 *
	 * @return \StdClass validated response object data (as object, not array)
	 *
	 * @throws Ex\IncompatibleTypeException
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws \ReflectionException
	 */
    public function getResponseSuccessObject(int $expected_api_code_offset = null,
                                             int $expected_http_code = null,
                                             string $expected_message = null): \stdClass
    {
        if ($expected_api_code_offset === null) {
            /** @var BaseApiCodes $api_codes */
            $api_codes = $this->getApiCodesClassName();
            $expected_api_code_offset = $this->getProtectedConstant($api_codes, 'OK_OFFSET');
        }

        $expected_http_code = $expected_http_code ?? RB::DEFAULT_HTTP_CODE_OK;
        if (($expected_http_code < 200) || ($expected_http_code > 299)) {
            $this->fail("TEST: Success HTTP code ($expected_http_code) in not in range: 200-299.");
        }

        if ($expected_message === null) {
            $key = \MarcinOrlowski\ResponseBuilder\BaseApiCodes::getCodeMessageKey($expected_api_code_offset);
            $key = $key ?? \MarcinOrlowski\ResponseBuilder\BaseApiCodes::getCodeMessageKey(
                    \MarcinOrlowski\ResponseBuilder\BaseApiCodes::OK());
            /** @var string $key */
            $expected_message = $this->langGet($key, ['api_code' => $expected_api_code_offset]);
        }

        $j = $this->getResponseObjectRaw($expected_api_code_offset, $expected_http_code, $expected_message);
        $this->assertEquals(true, $j->{RB::KEY_SUCCESS});

        return $j;
    }


	/**
	 * Retrieves and validates response as expected from errorXXX() methods
	 *
	 * @param int|null    $expected_api_code_offset expected Api response code offset or @null for default value
	 * @param int         $expected_http_code       Expected HTTP code
	 * @param string|null $message                  Expected return message or @null if we automatically mapped message fits
	 *
	 * @return \StdClass response object built from JSON
	 *
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\IncompatibleTypeException
	 */
    public function getResponseErrorObject(int $expected_api_code_offset = null,
                                           int $expected_http_code = RB::DEFAULT_HTTP_CODE_ERROR,
                                           string $message = null): \stdClass
    {
        if ($expected_api_code_offset === null) {
            /** @var BaseApiCodes $api_codes_class_name */
            $api_codes_class_name = $this->getApiCodesClassName();
	        $expected_api_code_offset = $api_codes_class_name::NO_ERROR_MESSAGE();
        }

        if ($expected_http_code > RB::ERROR_HTTP_CODE_MAX) {
            $this->fail(\sprintf('TEST: Error HTTP code (%d) cannot be above %d',
                $expected_http_code, RB::ERROR_HTTP_CODE_MAX));
        }
        if ($expected_http_code < RB::ERROR_HTTP_CODE_MIN) {
            $this->fail(\sprintf('TEST: Error HTTP code (%d) cannot be below %d',
                $expected_http_code, RB::ERROR_HTTP_CODE_MIN));
        }

        $j = $this->getResponseObjectRaw($expected_api_code_offset, $expected_http_code, $message);
        $this->assertEquals(false, $j->success);

        return $j;
    }


	/**
	 * @param int         $expected_api_code  expected Api response code offset
	 * @param int         $expected_http_code expected HTTP code
	 * @param string|null $expected_message   expected message string or @null if default
	 *
	 * @return mixed
	 *
	 * @throws Ex\IncompatibleTypeException
	 * @throws Ex\MissingConfigurationKeyException
	 */
    private function getResponseObjectRaw(int $expected_api_code, int $expected_http_code,
                                          string $expected_message = null)
    {
        $actual = $this->response->getStatusCode();
        $contents = $this->getResponseContent($this->response);
        $this->assertEquals($expected_http_code, $actual,
            "Expected status code {$expected_http_code}, got {$actual}. Response: {$contents}");

        // get response as Json object
        $j = \json_decode($this->getResponseContent($this->response), false);

        $this->assertEquals($expected_api_code, $j->code);

        /** @var BaseApiCodes $api_codes_class_name */
        $api_codes_class_name = $this->getApiCodesClassName();

		if ($expected_message === null) {
			$key = $api_codes_class_name::getCodeMessageKey($expected_api_code);
			Validator::assertIsString('key', $key);
			/** @var string $key */
			$expected_message_string = $this->langGet($key, ['api_code' => $expected_api_code]);
		} else {
			$expected_message_string = $expected_message;
		}
	    $this->assertEquals($expected_message_string, $j->message);

        return $j;
    }

    /**
     * Validates if given $json_object contains all expected elements
     *
     * @param \StdClass $json_object JSON Object holding Api response to validate
     *
     * @return void
     */
    public function assertValidResponse(\stdClass $json_object): void
    {
        $this->assertIsObject($json_object);
        $this->assertIsBool($json_object->{RB::KEY_SUCCESS});
        $this->assertIsInt($json_object->code);
        $this->assertIsString($json_object->locale);
        /** @noinspection UnNecessaryDoubleQuotesInspection */
        $this->assertNotEquals(\trim($json_object->locale), '', "'locale' cannot be empty string");
	    $this->assertIsString($json_object->message);
	    $this->assertTrue(($json_object->data === null) || \is_object($json_object->data),
		    "Response 'data' must be either object or null");
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Checks if Response's code matches our expectations. If not, shows
     * \MarcinOrlowski\ResponseBuilder\ApiCodeBase::XXX constant name of expected and current values
     *
     * @param int       $expected_code ApiCode::XXX code expected
     * @param \StdClass $response_json response json object
     *
     * @return void
     */
    public function assertResponseStatusCode(int $expected_code, \stdClass $response_json): void
    {
        $response_code = $response_json->code;

        if ($response_code !== $expected_code) {
            $msg = \sprintf('Status code mismatch. Expected: %s, found %s. Message: "%s"',
                $this->resolveConstantFromCode($expected_code),
                $this->resolveConstantFromCode($response_code),
                $response_json->message);

            $this->fail($msg);
        }
    }

    // -----------------------------------------------------------------------------------------------------------

	/**
	 * Calls protected method make()
	 *
	 * @param boolean    $success                    @true if response should indicate success, @false otherwise
	 * @param int        $api_code_offset            API code to use with produced response
	 * @param string|int $message_or_api_code_offset Resolvable Api code or message string
	 * @param array|null $data                       Data to return
	 * @param array|null $headers                    HTTP headers to include
	 * @param int|null   $encoding_options           see http://php.net/manual/en/function.json-encode.php
	 * @param array|null $debug_data                 optional data to be included in response JSON
	 *
	 * @return HttpResponse
	 *
	 * @throws \ReflectionException
	 * @throws Ex\MissingConfigurationKeyException
	 *
	 * @noinspection PhpTooManyParametersInspection
	 */
    protected function callMakeMethod(bool $success, int $api_code_offset, $message_or_api_code_offset,
                                      array $data = null,
                                      array $headers = null, int $encoding_options = null,
                                      array $debug_data = null): HttpResponse
    {
        if (!\is_bool($success)) {
            $this->fail(sprintf('"success" must be of type boolean ("%s" found)', \gettype($success)));
        }

        $http_code = null;
        $lang_args = null;

        return $this->callProtectedMethod(
            RB::asSuccess(), 'make', [$success,
                                           $api_code_offset,
                                           $message_or_api_code_offset,
                                           $data,
                                           $http_code,
                                           $lang_args,
                                           $headers,
                                           $encoding_options,
                                           $debug_data]);
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Returns ErrorCode constant name referenced by its value
     *
     * @param int $api_code_offset value to match constant name for
     *
     * @return int|null|string
     */
    protected function resolveConstantFromCode(int $api_code_offset)
    {
        /** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
        $api_codes_class_name = $this->getApiCodesClassName();
        $const = $api_codes_class_name::getApiCodeConstants();
        $name = null;
        foreach ($const as $const_name => $const_value) {
            if (\is_int($const_value) && ($const_value === $api_code_offset)) {
                $name = $const_name;
                break;
            }
        }

        return $name ?? "??? ({$api_code_offset})";
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Calls protected method of $object, passing optional array of arguments.
     *
     * @param object|string $obj_or_cls  Object to call $method_name on or name of the class.
     * @param string        $method_name Name of method to called.
     * @param array         $args        Optional array of arguments (empty array if no args to pass).
     *
     * @return mixed
     *
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    protected function callProtectedMethod($obj_or_cls, string $method_name, array $args = [])
    {
	    Validator::assertIsObjectOrExistingClass('obj_or_cls', $obj_or_cls);

	    /**
	     * At this point $obj_or_cls is either object or string but some static analyzers
	     * got problems figuring that out, so this (partially correct) var declaration is
	     * to make them believe.
	     *
	     * @var object $obj_or_cls
	     */
        $reflection = new \ReflectionClass($obj_or_cls);
        $method = $reflection->getMethod($method_name);
        $method->setAccessible(true);

        return $method->invokeArgs(\is_object($obj_or_cls) ? $obj_or_cls : null, $args);
    }

    /**
     * Returns value of otherwise non-public member of the class
     *
     * @param string|object $obj_or_cls class name to get member from, or instance of that class
     * @param string        $name       member name to grab (i.e. `max_length`)
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    protected function getProtectedMember($obj_or_cls, string $name)
    {
	    Validator::assertIsObjectOrExistingClass('obj_or_cls', $obj_or_cls);

	    /**
	     * At this point $obj_or_cls is either object or string but some static analyzers
	     * got problems figuring that out, so this (partially correct) var declaration is
	     * to make them believe.
	     *
	     * @var object $obj_or_cls
	     */
	    $reflection = new \ReflectionClass($obj_or_cls);
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue(is_object($obj_or_cls) ? $obj_or_cls : null);
    }

    /**
     * Returns value of otherwise non-public member of the class
     *
     * @param string|object $obj_or_cls class name to get member from, or instance of that class
     * @param string        $name       name of constant to grab (i.e. `FOO`)
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function getProtectedConstant($obj_or_cls, string $name)
    {
    	Validator::assertIsObjectOrExistingClass('obj_or_cls', $obj_or_cls);

	    /**
	     * At this point $obj_or_cls is either object or string but some static analyzers
	     * got problems figuring that out, so this (partially correct) var declaration is
	     * to make them believe.
	     *
	     * @var object $obj_or_cls
	     */
	    return (new \ReflectionClass($obj_or_cls))->getConstant($name);
    }

	/**
	 * Generates random string, with optional prefix
	 *
	 * @param string|null $prefix
	 *
	 * @return string
	 */
    protected function getRandomString(string $prefix = null): string
    {
        if ($prefix !== null) {
            $prefix = "{$prefix}_";
        }

        return $prefix . \md5(uniqid('', true));
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * UTF8 aware version of ord()
     *
     * @param string $string UTF8 string to work on
     * @param int    $offset start offset. Note, offset will be updated to properly skip multi-byte chars!
     *
     * $text = "abcàêß€abc";
     * $offset = 0;
     * while ($offset >= 0) {
     *    printf("%d: %d\n", $offset, ord8($text, $offset));
     * }
     *
     * @return int code of the character
     */
    protected function ord8(string $string, int &$offset)
    {
        $code = \ord($string[ $offset ]);
        $bytes_number = 1;
        if ($code >= 128) {             //otherwise 0xxxxxxx
            if ($code < 224) {          //110xxxxx
                $bytes_number = 2;
            } elseif ($code < 240) {    //1110xxxx
                $bytes_number = 3;
            } elseif ($code < 248) {    //11110xxx
                $bytes_number = 4;
            }

            $tmp = $code - 192 - ($bytes_number > 2 ? 32 : 0) - ($bytes_number > 3 ? 16 : 0);
            for ($i = 2; $i <= $bytes_number; $i++) {
                $offset++;
                $code2 = \ord(\substr($string, $offset, 1)) - 128;        //10xxxxxx
                $tmp = $tmp * 64 + $code2;
            }
            $code = $tmp;
        }
        $offset++;
        if ($offset >= \strlen($string)) {
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
    protected function escape8(string $string): string
    {
        $escaped = '';

        // escape UTF8 for further comparision
        $offset = 0;
        while ($offset >= 0) {
            $escaped .= \sprintf('\u%04x', $this->ord8($string, $offset));
        }

        return $escaped;
    }

}
