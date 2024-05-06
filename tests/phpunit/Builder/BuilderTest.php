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

use MarcinOrlowski\PhpunitExtraAsserts\Generator;
use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Builder;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class BuilderTest
 */
class BuilderTest extends TestCase
{
    /**
     * Check plain success() invocation
     */
    public function testSuccess(): void
    {
        $expected_api_code = BaseApiCodes::OK();

        $builder = RB::asSuccess($expected_api_code);
        $this->assertInstanceOf(RB::class, $builder);
        $this->response = $builder->build();

        $api = $this->getResponseSuccessObject();

        $this->assertNull($api->getData());
        $msg_key = BaseApiCodes::getCodeMessageKey($expected_api_code);
        /** @var string $msg_key */
        $this->assertEquals($this->langGet($msg_key), $api->getMessage());
    }

    /**
     * Checks if custom headers are properly used in the response.
     */
    public function testWithHttpHeaders(): void
    {
        $key1 = Generator::getRandomString('key1');
        $val1 = Generator::getRandomString('val1');
        $key2 = Generator::getRandomString('key2');
        $val2 = Generator::getRandomString('val2');
        $key3 = Generator::getRandomString('key3');
        $val3 = Generator::getRandomString('val3');
        $key4 = Generator::getRandomString('key4');
        $val4 = Generator::getRandomString('val4');

        $headers = [
            $key1 => $val1,
            $key2 => $val2,
            $key3 => $val3,
            $key4 => $val4,
        ];

        $builder = RB::asSuccess();
        $this->assertInstanceOf(RB::class, $builder);
        $this->response = $builder
            ->withHttpHeaders($headers)
            ->build();

        foreach ($headers as $key => $val) {
            $this->assertTrue($this->response->headers->has($key));
            $this->assertEquals($val, $this->response->headers->get($key));
        }
    }

    /**
     * Checks if exception is thrown while attempting to build response indicating failure with api_code
     * indicating success.
     */
    public function testErrorWithOkCode(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        RB::asError(BaseApiCodes::OK());
    }

} // end of class
