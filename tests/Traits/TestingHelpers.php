<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Traits;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\Lockpick\Lockpick;
use MarcinOrlowski\ResponseBuilder\ApiResponse;
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

    // ---------------------------------------------------------------------------------------------

    /**
     * Sets up testing environment
     *
     * @throws \ReflectionException
     */
    public function setUp(): void
    {
        parent::setUp();

        // Obtain configuration params
        $class_name = $this->getApiCodesClassName();

        $obj = new $class_name();
        /** @var int $min */
        $min = Lockpick::call($obj, 'getMinCode');
        $this->min_allowed_code = $min;
        /** @var int $max */
        $max = Lockpick::call($obj, 'getMaxCode');
        $this->max_allowed_code = $max;

        // generate random api_code
        /** @noinspection RandomApiMigrationInspection */
        $this->random_api_code = \mt_rand($this->min_allowed_code, $this->max_allowed_code);

        // AND corresponding mapped message mapping
        $map = Lockpick::call(new BaseApiCodes(), 'getBaseMap');
        /** @var array $map */
        if (empty($map)) {
            throw new \RuntimeException('getBaseMap() returned empty value.');
        }
        $idx = \random_int(1, \count($map));
        $this->random_api_code_message_key = $map[ \array_keys($map)[ $idx - 1 ] ];
        $this->random_api_code_message = $this->langGet($this->random_api_code_message_key,
            ['api_code' => $this->random_api_code,]);

        $this->error_message_map = [
            $this->random_api_code => $this->random_api_code_message_key,
        ];
        \Config::set(RB::CONF_KEY_MAP, $this->error_message_map);
    }

    // ---------------------------------------------------------------------------------------------

    /**
     * We wrap call to response's getContent() to handle case of `false` being return value.
     */
    public function getResponseContent(HttpResponse $response): string
    {
        $content = $response->getContent();
        if ($content === false) {
            throw new \LogicException('Response does not contains any content.');
        }
        return $content;
    }

    // ---------------------------------------------------------------------------------------------

    /**
     * As Lang::get() wrapper can also return whole translation array, not only single strings,
     * this make static code analysers unhappy as its signature indicates it can return arrays too, which we
     * do not want to happen, not handle separately after each invocation, so this wrapper deals with it for
     * us.
     *
     * @param string     $key     String key as passed to Lang::get()
     * @param array|null $replace Optional replacement array as passed to Lang::get()
     */
    public function langGet(string $key, ?array $replace = null): string
    {
        $replace = $replace ?? [];
        $result = \Lang::get($key, $replace);
        if (is_array($result)) {
            $result = implode('', $result);
        }
        return $result;
    }

    // ---------------------------------------------------------------------------------------------

    /**
     * Checks if response object was returned with expected success HTTP code (200-299) indicating
     * API method executed successfully. Returns validated response object data (as object,
     * not an array).
     *
     * NOTE: content of `data` node is NOT checked here!
     *
     * @param int|null    $expected_api_code_offset expected api code offset or @null for default value
     * @param int|null    $expected_http_code       HTTP return code to check against or @null for default
     * @param string|null $expected_message         Expected value of 'message' or @null for default message
     *
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\MissingConfigurationKeyException
     * @throws \ReflectionException
     */
    public function getResponseSuccessObject(?int    $expected_api_code_offset = null,
                                             int     $expected_http_code = null,
                                             ?string $expected_message = null): ApiResponse
    {
        if ($expected_api_code_offset === null) {
            /** @var BaseApiCodes $api_codes */
            $api_codes = $this->getApiCodesClassName();
            /** @var int $expected_api_code_offset */
            $expected_api_code_offset = Lockpick::getConstant($api_codes, 'OK_OFFSET');
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

        $api = $this->getResponseObjectRaw($expected_api_code_offset, $expected_http_code, $expected_message);

        $this->assertEquals(true, $api->success());

        return $api;
    }

    /**
     * Retrieves and validates response as expected from errorXXX() methods. Returns response
     * object built from JSON.
     *
     * @param int|null    $expected_api_code_offset expected Api response code offset or @null for default
     *                                              value
     * @param int         $expected_http_code       Expected HTTP code
     * @param string|null $message                  Expected return message or @null if we automatically
     *                                              mapped message fits
     *
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\IncompatibleTypeException
     */
    public function getResponseErrorObject(?int    $expected_api_code_offset = null,
                                           int     $expected_http_code = RB::DEFAULT_HTTP_CODE_ERROR,
                                           ?string $message = null): ApiResponse
    {
        if ($expected_api_code_offset === null) {
            /** @var BaseApiCodes $api_codes_class_name */
            $api_codes_class_name = $this->getApiCodesClassName();
            $expected_api_code_offset = $api_codes_class_name::NO_ERROR_MESSAGE();
        }

        if ($expected_http_code > RB::ERROR_HTTP_CODE_MAX) {
            $this->fail(\sprintf('TEST: Error HTTP code (%d) cannot be higher than %d',
                $expected_http_code, RB::ERROR_HTTP_CODE_MAX));
        }
        if ($expected_http_code < RB::ERROR_HTTP_CODE_MIN) {
            $this->fail(\sprintf('TEST: Error HTTP code (%d) cannot be lower than %d',
                $expected_http_code, RB::ERROR_HTTP_CODE_MIN));
        }

        $api = $this->getResponseObjectRaw($expected_api_code_offset, $expected_http_code, $message);

        $this->assertEquals(false, $api->success());

        return $api;
    }


    /**
     * @param int         $expected_api_code  expected Api response code offset
     * @param int         $expected_http_code expected HTTP code
     * @param string|null $expected_message   expected message string or @null if default
     *
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\MissingConfigurationKeyException
     */
    private function getResponseObjectRaw(int     $expected_api_code,
                                          int     $expected_http_code,
                                          ?string $expected_message = null): ApiResponse
    {
        $actual = $this->response->getStatusCode();
        $contents = $this->getResponseContent($this->response);
        $this->assertEquals($expected_http_code, $actual,
            "Expected status code {$expected_http_code}, got {$actual}. Response: {$contents}");

        // get response
        $api = ApiResponse::fromJson($this->getResponseContent($this->response));

        $this->assertEquals($expected_api_code, $api->getCode());

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
        $this->assertEquals($expected_message_string, $api->getMessage());

        return $api;
    }

    // ---------------------------------------------------------------------------------------------

    /**
     * Checks if Response's code matches our expectations. If not, shows
     * \MarcinOrlowski\ResponseBuilder\ApiCodeBase::XXX constant name of expected and current values
     *
     * @param int       $expected_code ApiCode::XXX code expected
     * @param \StdClass $response_json response json object
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

    // ---------------------------------------------------------------------------------------------

    /**
     * Calls protected method make()
     *
     * @param boolean    $success                    @true if response should indicate success, @false
     *                                               otherwise
     * @param int        $api_code_offset            API code to use with produced response
     * @param string|int $message_or_api_code_offset Resolvable Api code or message string
     * @param array|null $data                       Data to return
     * @param array|null $headers                    HTTP headers to include
     * @param int|null   $encoding_options           see http://php.net/manual/en/function.json-encode.php
     * @param array|null $debug_data                 optional data to be included in response JSON
     *
     * @throws \ReflectionException
     * @throws Ex\MissingConfigurationKeyException
     *
     * @noinspection PhpTooManyParametersInspection
     */
    protected function callMakeMethod(bool       $success,
                                      int        $api_code_offset,
                                      string|int $message_or_api_code_offset,
                                      ?array     $data = null,
                                      ?array     $headers = null,
                                      ?int       $encoding_options = null,
                                      ?array     $debug_data = null): HttpResponse
    {
        $http_code = null;
        $lang_args = null;

        $result = Lockpick::call(
            RB::asSuccess(), 'make', [$success,
                                      $api_code_offset,
                                      $message_or_api_code_offset,
                                      $data,
                                      $http_code,
                                      $lang_args,
                                      $headers,
                                      $encoding_options,
                                      $debug_data,
        ]);

        /** @var HttpResponse $result */
        return $result;
    }

    // ---------------------------------------------------------------------------------------------

    /**
     * Returns ErrorCode constant name referenced by its value. Note, will return
     * first one spotted with that value so this is pretty fragile.
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

    // ---------------------------------------------------------------------------------------------

    /**
     * UTF8 aware version of ord(). Returns code of the character at given string offset.
     *
     * @param string $string UTF8 string to work on
     * @param int    $offset start offset. Note, offset will be updated to properly skip multi-byte chars!
     *
     * $text = "abcàêß€abc";
     * $offset = 0;
     * while ($offset >= 0) {
     *    printf("%d: %d\n", $offset, ord8($text, $offset));
     * }
     */
    protected function ord8(string $string, int &$offset): int
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

} // end of class
