<?php
/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Builder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\ApiResponse;
use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Tests RB::make() method
 */
class MakeTest extends TestCase
{
    /**
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testCustomMessageAndCodeOutOfRange(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $api_code = $this->max_allowed_code + 1;    // invalid
        $this->callMakeMethod(true, $api_code, 'message');
    }

    /**
     * Validates make() handling invalid type of encoding_options
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testInvalidEncodingOptions(): void
    {
        $this->expectException(Ex\InvalidTypeException::class);

        /** @noinspection PhpUndefinedClassInspection */
        \Config::set(RB::CONF_KEY_ENCODING_OPTIONS, []);
        $this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK());
    }

    /**
     * Tests fallback to default encoding_options
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testDefaultEncodingOptions(): void
    {
        // source data
        $test_string = 'ąćę';
        $data = ['test' => $test_string];

        // fallback defaults in action
        \Config::offsetUnset('encoding_options');
        $resp = $this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK(), $data);

        $matches = [];
        $this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $this->getResponseContent($resp), $matches));
        $result_defaults = $matches[1];


        // check if it returns the same when defaults enforced explicitly
        $resp = $this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK(), $data,
            null, RB::DEFAULT_ENCODING_OPTIONS);

        $matches = [];
        $this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $this->getResponseContent($resp), $matches));
        $result_defaults_enforced = $matches[1];

        $this->assertEquals($result_defaults, $result_defaults_enforced);
    }

    /**
     * Checks encoding_options influences result JSON data
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testValidateEncodingOptionsPreventsEscaping(): void
    {
        $test_string = 'ąćę';
        $test_string_escaped = $this->escape8($test_string);

        // source data
        $data = ['test' => $test_string];

        // check if it returns escaped
        /** @noinspection PhpUndefinedClassInspection */
        \Config::set(RB::CONF_KEY_ENCODING_OPTIONS, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $resp = $this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK(), $data);

        $matches = [];
        $this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $this->getResponseContent($resp), $matches));
        $result_escaped = $matches[1];
        $this->assertEquals($test_string_escaped, $result_escaped);

        // check if it returns unescaped
        \Config::set(RB::CONF_KEY_ENCODING_OPTIONS,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
        $resp = $this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK(), $data);

        $matches = [];
        $this->assertNotEquals(0, preg_match('/^.*"test":"(.*)".*$/', $this->getResponseContent($resp), $matches));
        $result_unescaped = $matches[1];
        $this->assertEquals($test_string, $result_unescaped);

        // this one is just in case...
        $this->assertNotEquals($result_escaped, $result_unescaped);
    }

    /**
     * Checks if RB::CONF_KEY_DATA_ALWAYS_OBJECT correctly resturns NULL payload
     * as empty JSON Object
     */
    public function testDataAlwaysObjectConfigFlag(): void
    {
        // When enabling data_always_object feature
        \Config::set(RB::CONF_KEY_DATA_ALWAYS_OBJECT, true);

        // and passing NULL as data
        $data = null;
        $resp = $this->callMakeMethod(true, BaseApiCodes::OK(), BaseApiCodes::OK(), $data);

        // returned 'data' branch should be empty JSON object
        $j = ApiResponse::fromJson($this->getResponseContent($resp));
        $this->assertEquals(true, $j->success());
        $data = $j->getData();
        $this->assertNotNull($data);
        $this->assertEmpty($data);
    }

} // end of class
