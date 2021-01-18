<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

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

class DummyResponseBuilder extends \MarcinOrlowski\ResponseBuilder\ResponseBuilder
{
// dummy
}

class FactoryTest extends TestCase
{
	/**
	 * Checks if asSuccess() properly returns object of extending class
	 *
	 * @return void
	 */
	public function testAsSuccess(): void
	{
		$my_response_builder = DummyResponseBuilder::asSuccess();
		$this->assertEquals(DummyResponseBuilder::class, \get_class($my_response_builder));
	}

	/**
	 * Checks if asError(); properly returns object of extending class
	 *
	 * @return void
	 */
	public function testAsError(): void
	{
		$my_response_builder = DummyResponseBuilder::asError(BaseApiCodes::NO_ERROR_MESSAGE());
		$this->assertEquals(DummyResponseBuilder::class, \get_class($my_response_builder));
	}
}
