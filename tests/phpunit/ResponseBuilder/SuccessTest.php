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
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2021 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Type;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class SuccessTest
 *
 * @package MarcinOrlowski\ResponseBuilder\Tests
 */
class SuccessTest extends TestCase
{
	/**
	 * Check plain success() invocation
	 *
	 * @return void
	 */
	public function testSuccess(): void
	{
		$this->response = RB::success();
		$j = $this->getResponseSuccessObject(BaseApiCodes::OK());

		$this->assertNull($j->data);
		$msg_key = BaseApiCodes::getCodeMessageKey(BaseApiCodes::OK());
		/** @var string $msg_key */
		$this->assertEquals($this->langGet($msg_key), $j->message);
	}

	public function testSuccessWithArrayPayload(): void
	{
		$payload = [];
		for ($i = 0; $i < 10; $i++) {
			$payload[] = $this->getRandomString("item${i}");
		}

		$this->response = RB::success($payload);
		$j = $this->getResponseSuccessObject(BaseApiCodes::OK());

		$this->assertNotNull($j->data);
		$data = (array)$j->data;

		$cfg = Config::get(RB::CONF_KEY_CONVERTER_PRIMITIVES);
		$this->assertNotEmpty($cfg);
		$key = $cfg[ Type::ARRAY ][ RB::KEY_KEY ];

		$this->assertCount(1, $data);
		$this->assertArrayEquals($payload, (array)$j->data->{$key});

		$msg_key = BaseApiCodes::getCodeMessageKey(BaseApiCodes::OK());
		/** @var string $msg_key */
		$this->assertEquals($this->langGet($msg_key), $j->message);
	}

}
