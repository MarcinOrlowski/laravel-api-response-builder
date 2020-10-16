<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2020 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;


class SuccessTest extends TestCase
{
	/**
	 * Check plain success() invocation
	 *
	 * @return void
	 */
	public function testSuccess(): void
	{
		$this->response = ResponseBuilder::success();
		$j = $this->getResponseSuccessObject(BaseApiCodes::OK());

		$this->assertNull($j->data);
		$this->assertEquals(\Lang::get(BaseApiCodes::getCodeMessageKey(BaseApiCodes::OK())), $j->message);
	}

	public function testSuccessWithExplicitNull(): void
	{
		$this->response = ResponseBuilder::success(null);
		$j = $this->getResponseSuccessObject(BaseApiCodes::OK());

		$this->assertNull($j->data);
		$this->assertEquals(\Lang::get(BaseApiCodes::getCodeMessageKey(BaseApiCodes::OK())), $j->message);
	}

	public function testSuccessWithArrayPayload(): void
	{
		$payload = [];
		for ($i = 0; $i < 10; $i++) {
			$payload[] = $this->getRandomString("item${i}");
		}

		$this->response = ResponseBuilder::success($payload);
		$j = $this->getResponseSuccessObject(BaseApiCodes::OK());

		$this->assertNotNull($j->data);
		$this->assertArraysEquals($payload, (array)$j->data);
		$this->assertEquals(\Lang::get(BaseApiCodes::getCodeMessageKey(BaseApiCodes::OK())), $j->message);
	}

}
