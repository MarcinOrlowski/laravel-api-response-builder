<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

use MarcinOrlowski\ResponseBuilder\ApiCodeBase;

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
class ApiCodeBaseTest extends Base\ResponseBuilderTestCaseBase
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
		\Config::offsetUnset('response_builder.min_code');
		ApiCodeBase::getMinCode();
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
		\Config::offsetUnset('response_builder.max_code');
		ApiCodeBase::getMaxCode();
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
		\Config::offsetUnset('response_builder.map');
		ApiCodeBase::getMap();
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
		\Config::set('response_builder.map', false);
		ApiCodeBase::getMap();
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
		ApiCodeBase::getCodeMessageKey(ApiCodeBase::RESERVED_MAX_API_CODE + 1);
	}

	/**
	 * Tests getBaseCodeMessageKey() with too low code
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetBaseCodeMessageKey_TooLow()
	{
		ApiCodeBase::getBaseCodeMessageKey(ApiCodeBase::RESERVED_MIN_API_CODE - 1);
	}

	/**
	 * Tests getBaseCodeMessageKey() with too high code
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetBaseCodeMessageKey_TooHigh()
	{
		ApiCodeBase::getBaseCodeMessageKey(ApiCodeBase::RESERVED_MAX_API_CODE + 1);
	}

	/**
	 * Tests getBaseCodeMessageKey()
	 *
	 * @return void
	 */
	public function testGetBaseCodeMessageKey()
	{
		// check how mapped code handling works
		$mapping = ApiCodeBase::getBaseCodeMessageKey(ApiCodeBase::OK);
		$this->assertNotNull($mapping);

		// check how not-mapped code is handled
		$base_map = $this->getProtectedMember(ApiCodeBase::class, 'base_map');

		for ($code = ApiCodeBase::RESERVED_MIN_API_CODE; $code < ApiCodeBase::RESERVED_MAX_API_CODE; $code++) {
			if (!array_key_exists($code, $base_map)) {
				break;
			}
		}

		$mapping = ApiCodeBase::getBaseCodeMessageKey($code);
		$this->assertNull($mapping);
	}

}
