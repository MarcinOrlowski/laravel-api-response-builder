<?php
declare(strict_types=1);
/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace MarcinOrlowski\ResponseBuilder\Tests\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Config;
use MarcinOrlowski\PhpunitExtraAsserts\ExtraAsserts;
use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Type;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;
use MarcinOrlowski\PhpunitExtraAsserts\Generator;

/**
 * Class SuccessTest
 */
class SuccessTest extends TestCase
{
    /**
     * Check plain success() invocation
     */
    public function testSuccess(): void
    {
        $this->response = RB::success();
        $api = $this->getResponseSuccessObject(BaseApiCodes::OK());

        $this->assertNull($api->getData());
        $msg_key = BaseApiCodes::getCodeMessageKey(BaseApiCodes::OK());
        /** @var string $msg_key */
        $this->assertEquals($this->langGet($msg_key), $api->getMessage());
    }

    public function testSuccessWithArrayPayload(): void
    {
        $payload = [];
        for ($i = 0; $i < 10; $i++) {
            $payload[] = Generator::getRandomString("item${i}");
        }

        $this->response = RB::success($payload);
        $api = $this->getResponseSuccessObject(BaseApiCodes::OK());

        $this->assertNotNull($api->getData());
        $data = (array)$api->getData();

        $cfg = Config::get(RB::CONF_KEY_CONVERTER_PRIMITIVES);
        $this->assertNotEmpty($cfg);
        $this->assertIsArray($cfg);
        /** @var array $cfg */
        $key = $cfg[ Type::ARRAY ][ RB::KEY_KEY ];

        $this->assertCount(1, $data);
        /** @var array $data */
        $data = $api->getData();
        ExtraAsserts::assertArrayEquals($payload, (array)$data[ $key ]);

        $msg_key = BaseApiCodes::getCodeMessageKey(BaseApiCodes::OK());
        /** @var string $msg_key */
        $this->assertEquals($this->langGet($msg_key), $api->getMessage());
    }

} // end of class
