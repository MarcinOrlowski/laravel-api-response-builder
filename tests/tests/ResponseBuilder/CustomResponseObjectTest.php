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

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class MyResponseBuilder extends \MarcinOrlowski\ResponseBuilder\ResponseBuilder
{
	public static $fake_response = [];

	protected function buildResponse(bool $success, int $api_code,
	                                 $msg_or_api_code, array $placeholders = null,
	                                 $data = null, array $debug_data = null): array
	{
		return static::$fake_response;
	}
}

class CustomResponseObjectTest extends TestCase
{
	/**
	 * Checks if asSuccess() properly returns object of extending class
	 *
	 * @return void
	 */
	public function testAsSuccess(): void
	{
		$my_response_builder = MyResponseBuilder::asSuccess();
		$this->assertEquals('MarcinOrlowski\ResponseBuilder\Tests\MyResponseBuilder', get_class($my_response_builder));
	}

	/**
	 * Checks if asError(); properly returns object of extending class
	 *
	 * @return void
	 */
	public function testAsError(): void
	{
		$my_response_builder = MyResponseBuilder::asError(BaseApiCodes::NO_ERROR_MESSAGE());
		$this->assertEquals('MarcinOrlowski\ResponseBuilder\Tests\MyResponseBuilder', get_class($my_response_builder));
	}


	/**
	 * Check if overring response object works.
	 *
	 * @return void
	 */
	public function testCustomResponse(): void
	{
		MyResponseBuilder::$fake_response = [];
		for ($i = 0; $i < 10; $i++) {
			MyResponseBuilder::$fake_response[ $this->getRandomString() ] = $this->getRandomString();
		}

		$response = MyResponseBuilder::success();
		$this->assertArraysEquals(MyResponseBuilder::$fake_response, json_decode($response->getContent(), true));
	}

}
