<?php
/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\PhpunitExtraAsserts\ExtraAsserts;
use MarcinOrlowski\PhpunitExtraAsserts\Generator;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class CustomResponseObjectTest
 */
class CustomResponseObjectTest extends TestCase
{
    /**
     * Check if overring response object works.
     */
    public function testCustomResponse(): void
    {
        /** @noinspection DisallowWritingIntoStaticPropertiesInspection */
        MyResponseBuilder::$fake_response = [];
        for ($i = 0; $i < 10; $i++) {
            /** @noinspection AmbiguousMethodsCallsInArrayMappingInspection */
            MyResponseBuilder::$fake_response[ Generator::getRandomString() ] = Generator::getRandomString();
        }

        $response = MyResponseBuilder::success();
        /** @var array $array_b */
        $array_b = \json_decode($this->getResponseContent($response), true);
        ExtraAsserts::assertArrayEquals(MyResponseBuilder::$fake_response, $array_b);
    }

} // end of class
