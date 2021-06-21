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
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2021 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Builder;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class BuilderTest
 *
 * @package MarcinOrlowski\ResponseBuilder\Tests
 */
class BuilderTest extends TestCase
{
    /**
     * Check plain success() invocation
     *
     * @return void
     */
    public function testSuccess(): void
    {
        $expected_api_code = BaseApiCodes::OK();

        $builder = RB::asSuccess($expected_api_code);
        $this->assertInstanceOf(RB::class, $builder);
	    $this->response = $builder->build();

        $j = $this->getResponseSuccessObject();

        $this->assertNull($j->data);
        $msg_key = BaseApiCodes::getCodeMessageKey($expected_api_code);
        /** @var string $msg_key */
        $this->assertEquals($this->langGet($msg_key), $j->message);
    }

    /**
     * Checks if custom headers are properly used in the response.
     */
    public function testWithHttpHeaders(): void
    {
        $key1 = $this->getRandomString('key1');
        $val1 = $this->getRandomString('val1');
        $key2 = $this->getRandomString('key2');
        $val2 = $this->getRandomString('val2');
        $key3 = $this->getRandomString('key3');
        $val3 = $this->getRandomString('val3');
        $key4 = $this->getRandomString('key4');
        $val4 = $this->getRandomString('val4');

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
}
