<?php

namespace MarcinOrlowski\ResponseBuilder\Tests\Legacy;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilderLegacy;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ErrorTest extends TestCase
{
    /**
     * Check success()
     *
     * @return void
     */
    public function testError(): void
    {
        // GIVEN random error code
        $api_code = $this->random_api_code;

        // WHEN we report error
        $this->response = ResponseBuilderLegacy::error($api_code);

        // THEN returned message contains given error code and mapped message
        $j = $this->getResponseErrorObject($api_code);
        $this->assertEquals($this->random_api_code_message, $j->message);

        // AND no data
        $this->assertNull($j->data);
    }

    /**
     * Tests error() with various http codes and random payload.
     *
     * @return void
     */
    public function testErrorWithDataHttpCode(): void
    {
        $http_codes = [HttpResponse::HTTP_CONFLICT,
                       HttpResponse::HTTP_BAD_REQUEST,
                       HttpResponse::HTTP_FAILED_DEPENDENCY,
                       ResponseBuilderLegacy::DEFAULT_HTTP_CODE_ERROR,
        ];

        foreach ($http_codes as $http_code) {
            // GIVEN data
            $data = [$this->getRandomString('key') => $this->getRandomString('val')];

            // AND error code
            $api_code = $this->random_api_code;

            // WHEN we report error
            $this->response = ResponseBuilderLegacy::error($api_code, null, $data, $http_code);

            // THEN returned message contains given error code and mapped message
            $j = $this->getResponseErrorObject($api_code, $http_code);
            $this->assertEquals($this->random_api_code_message, $j->message);

            // AND passed data
            $this->assertEquals((object)$data, $j->data);
        }
    }

    /**
     * Tests errorWithData()
     *
     * @return void
     */
    public function testErrorWithData(): void
    {
        $data = [$this->getRandomString('key') => $this->getRandomString('val')];
        $api_code = $this->random_api_code;
        $this->response = ResponseBuilderLegacy::errorWithData($api_code, $data);

        $j = $this->getResponseErrorObject($api_code);
        $this->assertEquals((object)$data, $j->data);
    }

    /**
     * Tests errorWithDataAndHttpCode()
     *
     * @return void
     */
    public function testErrorWithDataAndHttpCode(): void
    {
        $http_codes = [
            HttpResponse::HTTP_CONFLICT,
            HttpResponse::HTTP_BAD_REQUEST,
            HttpResponse::HTTP_FAILED_DEPENDENCY,
            ResponseBuilderLegacy::DEFAULT_HTTP_CODE_ERROR,
        ];

        foreach ($http_codes as $http_code) {
            $data = [$this->getRandomString('key') => $this->getRandomString('val')];
            $this->response = ResponseBuilderLegacy::errorWithDataAndHttpCode($this->random_api_code, $data, $http_code);

            $j = $this->getResponseErrorObject($this->random_api_code, $http_code);
            $this->assertEquals((object)$data, $j->data);
        }
    }

    /**
     * Tests errorWithHttpCode()
     *
     * @return void
     */
    public function testErrorWithHttpCode(): void
    {
        $http_codes = [
            HttpResponse::HTTP_CONFLICT,
            HttpResponse::HTTP_BAD_REQUEST,
            HttpResponse::HTTP_FAILED_DEPENDENCY,
            ResponseBuilderLegacy::DEFAULT_HTTP_CODE_ERROR,
        ];

        foreach ($http_codes as $http_code) {
            $this->response = ResponseBuilderLegacy::errorWithHttpCode($this->random_api_code, $http_code);

            $j = $this->getResponseErrorObject($this->random_api_code, $http_code);
            $this->assertNull($j->data);
        }
    }

    /**
     * Tests errorWithMessageAndData()
     *
     * @return void
     */
    public function testErrorWithMessageAndData(): void
    {
        $data = [$this->getRandomString('key') => $this->getRandomString('val')];
        $api_code = $this->random_api_code;
        $error_message = $this->getRandomString('msg');
        $this->response = ResponseBuilderLegacy::errorWithMessageAndData($api_code, $error_message, $data);

        $j = $this->getResponseErrorObject($api_code, ResponseBuilderLegacy::DEFAULT_HTTP_CODE_ERROR, $error_message);
        $this->assertEquals($error_message, $j->message);
        $this->assertEquals((object)$data, $j->data);
    }

    /**
     * Tests errorWithMessageAndDataAndDebug()
     *
     * @return void
     */
    public function testErrorWithMessageAndDataAndDebug(): void
    {
        /** @noinspection PhpUndefinedClassInspection */
        $trace_key = \Config::get(ResponseBuilderLegacy::CONF_KEY_DEBUG_EX_TRACE_KEY, ResponseBuilderLegacy::KEY_TRACE);
        $trace_data = [
            $trace_key => (object)[
                $this->getRandomString('trace_key') => $this->getRandomString('trace_val'),
            ],
        ];

        $data = [$this->getRandomString('key') => $this->getRandomString('val')];
        $api_code = $this->random_api_code;
        $error_message = $this->getRandomString('msg');

        /** @noinspection PhpUndefinedClassInspection */
        \Config::set(ResponseBuilderLegacy::CONF_KEY_DEBUG_EX_TRACE_ENABLED, true);
        $this->response = ResponseBuilderLegacy::errorWithMessageAndDataAndDebug($api_code, $error_message,
            $data, null, null, $trace_data);

        $j = $this->getResponseErrorObject($api_code, ResponseBuilderLegacy::DEFAULT_HTTP_CODE_ERROR, $error_message);
        $this->assertEquals($error_message, $j->message);
        $this->assertEquals((object)$data, $j->data);

        /** @noinspection PhpUndefinedClassInspection */
        $debug_key = \Config::get(ResponseBuilderLegacy::CONF_KEY_DEBUG_DEBUG_KEY, ResponseBuilderLegacy::KEY_DEBUG);
        $this->assertEquals((object)$trace_data, $j->$debug_key);
    }

    /**
     * Tests errorWithMessage()
     *
     * @return void
     */
    public function testErrorWithMessage(): void
    {
        $api_code = $this->random_api_code;
        $error_message = $this->getRandomString('msg');
        $this->response = ResponseBuilderLegacy::errorWithMessage($api_code, $error_message);

        $j = $this->getResponseErrorObject($api_code, ResponseBuilderLegacy::DEFAULT_HTTP_CODE_ERROR, $error_message);
        $this->assertEquals($error_message, $j->message);
        $this->assertNull($j->data);
    }

    /**
     * Checks if using errorXXX() with OK() code triggers resistance.
     *
     * @return void
     */
    public function testErrorWithOkCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ResponseBuilderLegacy::error(BaseApiCodes::OK());
    }

    /**
     * Tests error() handling api code with no message mapping.
     *
     * @return void
     */
    public function testErrorMissingMessageMapping(): void
    {
        /** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
        $api_codes_class_name = $this->getApiCodesClassName();

        // FIXME we **assume** this code is not set nor mapped. But assumptions suck...
        $api_code = $this->max_allowed_code - 1;
        $this->response = ResponseBuilderLegacy::error($api_code);

        $key = $api_codes_class_name::getCodeMessageKey($api_codes_class_name::NO_ERROR_MESSAGE());
        $lang_args = ['api_code' => $api_code];
        /** @noinspection PhpUndefinedClassInspection */
        $msg = \Lang::get($key, $lang_args);

        $j = $this->getResponseErrorObject($api_code, ResponseBuilderLegacy::DEFAULT_HTTP_CODE_ERROR, $msg);
        $this->assertNull($j->data);
    }

    /**
     * Checks if overriding built-in code->msg mapping works as expected.
     */
    public function testErrorWithCustomMessageConfigForCode(): void
    {
        // The $msg_key pretends to be regular Laravel localized string
        // key (like `api.my_error`). As the string it points is not available
        // Laravel will return the key itself,  which is perfectly sufficient
        // for us to ensure custom config settings are respected.
        $msg_key = $this->getRandomString('str_key');
        $api_code = $this->min_allowed_code;
        \Config::set(ResponseBuilderLegacy::CONF_KEY_MAP, [$api_code => $msg_key]);

        // Building error response with that api_code
        $this->response = ResponseBuilderLegacy::error($api_code);

        // Should return a response object with our $msg_key as message.
        $j = $this->getResponseErrorObject($api_code, ResponseBuilderLegacy::DEFAULT_HTTP_CODE_ERROR, $msg_key);
        $this->assertEquals($msg_key, $j->message);
    }

}
