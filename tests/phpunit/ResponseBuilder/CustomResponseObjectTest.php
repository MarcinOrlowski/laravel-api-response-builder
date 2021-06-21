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

use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class CustomResponseObjectTest
 *
 * @package MarcinOrlowski\ResponseBuilder\Tests
 */
class CustomResponseObjectTest extends TestCase
{
	/**
	 * Check if overring response object works.
	 *
	 * @return void
	 */
	public function testCustomResponse(): void
	{
		/** @noinspection DisallowWritingIntoStaticPropertiesInspection */
		MyResponseBuilder::$fake_response = [];
		for ($i = 0; $i < 10; $i++) {
			/** @noinspection AmbiguousMethodsCallsInArrayMappingInspection */
			MyResponseBuilder::$fake_response[ $this->getRandomString() ] = $this->getRandomString();
		}

		$response = MyResponseBuilder::success();
		$this->assertArrayEquals(MyResponseBuilder::$fake_response, json_decode($this->getResponseContent($response), true));
	}
}
