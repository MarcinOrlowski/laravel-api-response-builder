<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */
class ApiCodeBaseTest extends TestCase
{
	/**
	 * Tests getMinCode() with invalid config
	 *
	 * @return void
	 *
	 * @expectedException \RuntimeException
	 */
	public function testGetMinCode_MissingConfigKey()
	{
		\Config::offsetUnset(ResponseBuilder::CONF_KEY_MIN_CODE);
		BaseApiCodes::getMinCode();
	}

	/**
	 * Tests getMaxCode() with invalid config
	 *
	 * @return void
	 *
	 * @expectedException \RuntimeException
	 */
	public function testGetMaxCode_MissingConfigKey()
	{
		\Config::offsetUnset(ResponseBuilder::CONF_KEY_MAX_CODE);
		BaseApiCodes::getMaxCode();
	}


	/**
	 * Tests getMap() with missing config
	 *
	 * @return void
	 *
	 * @expectedException \RuntimeException
	 */
	public function testGetMap_MissingConfigKey()
	{
		\Config::offsetUnset(ResponseBuilder::CONF_KEY_MAP);
		BaseApiCodes::getMap();
	}

	/**
	 * Tests getMap() with wrong config
	 *
	 * @return void
	 *
	 * @expectedException \RuntimeException
	 */
	public function testGetMap_WrongConfig()
	{
		\Config::set(ResponseBuilder::CONF_KEY_MAP, false);
		BaseApiCodes::getMap();
	}


	/**
	 * Tests getCodeMessageKey() for code outside of allowed range
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetCodeMessageKey_OutOfRange()
	{
		BaseApiCodes::getCodeMessageKey(BaseApiCodes::RESERVED_MAX_API_CODE + 1);
	}

	/**
	 * Tests getReservedCodeMessageKey() with too low code
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetReservedCodeMessageKey_TooLow()
	{
		BaseApiCodes::getReservedCodeMessageKey(BaseApiCodes::RESERVED_MIN_API_CODE - 1);
	}

	/**
	 * Tests getReservedCodeMessageKey() with too high code
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetReservedCodeMessageKey_TooHigh()
	{
		BaseApiCodes::getReservedCodeMessageKey(BaseApiCodes::RESERVED_MAX_API_CODE + 1);
	}

	/**
	 * Tests getReservedCodeMessageKey()
	 *
	 * @return void
	 */
	public function testGetReservedCodeMessageKey()
	{
		// check how mapped code handling works
		$mapping = BaseApiCodes::getReservedCodeMessageKey(BaseApiCodes::OK);
		$this->assertNotNull($mapping);

		// check how not-mapped code is handled
		$base_map = $this->getProtectedMember(BaseApiCodes::class, 'base_map');

		for ($code = BaseApiCodes::RESERVED_MIN_API_CODE; $code < BaseApiCodes::RESERVED_MAX_API_CODE; $code++) {
			if (!array_key_exists($code, $base_map)) {
				break;
			}
		}

		$mapping = BaseApiCodes::getReservedCodeMessageKey($code);
		$this->assertNull($mapping);
	}

}
