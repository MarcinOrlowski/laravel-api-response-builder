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

class MyResponseBuilder extends \MarcinOrlowski\ResponseBuilder\ResponseBuilder
{
	public static $fake_response = [];

	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @param bool       $success
	 * @param int        $api_code
	 * @param int|string $msg_or_api_code
	 * @param array|null $placeholders
	 * @param null       $data
	 * @param array|null $debug_data
	 *
	 * @return array
	 */
	protected function buildResponse(bool $success, int $api_code,
	                                 $msg_or_api_code, array $placeholders = null,
	                                 $data = null, array $debug_data = null): array
	{
		return static::$fake_response;
	}
}

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
		$this->assertArrayEquals(MyResponseBuilder::$fake_response, json_decode($response->getContent(), true));
	}
}
