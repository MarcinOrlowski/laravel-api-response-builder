<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

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
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class InternalsTest extends TestCase
{
    /**
     * Tests if dist's config defaults matches ResponseBuilder::DEFAULT_ENCODING_OPTIONS
     *
     * @return void
     */
    public function testDefaultEncodingOptionValue(): void
    {
        $config_defaults = \Config::get(ResponseBuilder::CONF_KEY_ENCODING_OPTIONS);
        $this->assertEquals($config_defaults, ResponseBuilder::DEFAULT_ENCODING_OPTIONS);
    }

    /**
     * Validates handling of incomplete class mapping configuration by getClassesMapping()
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGetClassesMappingWithWrongType(): void
    {
        \Config::set(ResponseBuilder::CONF_KEY_CLASSES, false);

        $this->expectException(\RuntimeException::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->callProtectedMethod(Converter::class, 'getClassesMapping');
    }

    /**
     * Tests if getClassesMapping() would throw an exception with incomplete
     * class mapping configuration.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGetClassesMappingMethodWithIncompleteMappingConfiguration(): void
    {
        \Config::set(ResponseBuilder::CONF_KEY_CLASSES, [
            self::class => [],
        ]);

        $this->expectException(\RuntimeException::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->callProtectedMethod(Converter::class, 'getClassesMapping');
    }

    /**
     * Tests getCodeForInternalOffset() out of bounds handling
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGetCodeForInternalOffsetMethodWithOffsetOutOfMaxBounds(): void
    {
        $obj = new BaseApiCodes();
        $max = $this->getProtectedConstant($obj, 'RESERVED_MAX_API_CODE_OFFSET');

        $this->expectException(\InvalidArgumentException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->callProtectedMethod($obj, 'getCodeForInternalOffset', [$max + 1]);
    }

    /**
     * Tests getCodeForInternalOffset() out of bounds handling.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGetCodeForInternalOffsetMethodWithOffsetOutOfMinBounds(): void
    {
        $obj = new BaseApiCodes();
        $min = $this->getProtectedConstant($obj, 'RESERVED_MIN_API_CODE_OFFSET');

        $this->expectException(\InvalidArgumentException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->callProtectedMethod($obj, 'getCodeForInternalOffset', [$min - 1]);
    }

    /**
     * Tests getCodeMessageKey() if given code of configured code range.
     *
     * @return void
     */
    public function testGetCodeMessageKeyMethodWithCodeOutOfCodeRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        BaseApiCodes::getCodeMessageKey(BaseApiCodes::getMaxCode() + 1);
    }

    /**
     * Tests if all mandatory constants are still members of the class.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGetApiCodeConstants(): void
    {
        $expected = [
            'RESERVED_MIN_API_CODE_OFFSET',
            'RESERVED_MAX_API_CODE_OFFSET',

            'OK_OFFSET',
            'NO_ERROR_MESSAGE_OFFSET',
//            'EX_HTTP_NOT_FOUND_OFFSET',
//            'EX_HTTP_SERVICE_UNAVAILABLE_OFFSET',
            'EX_HTTP_EXCEPTION_OFFSET',
            'EX_UNCAUGHT_EXCEPTION_OFFSET',
//            'EX_AUTHENTICATION_EXCEPTION_OFFSET',
//            'EX_VALIDATION_EXCEPTION_OFFSET',
        ];
        /** @noinspection PhpUnhandledExceptionInspection */
        $consts = BaseApiCodes::getApiCodeConstants();

        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $consts);
        }
    }
}
