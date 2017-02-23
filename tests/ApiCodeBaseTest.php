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
	 * Tests getBaseMapping() with too low code
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetBaseMapping_TooLow()
	{
		ApiCodeBase::getBaseMapping(ApiCodeBase::RESERVED_MIN_API_CODE - 1);
	}

	/**
	 * Tests getBaseMapping() with too high code
	 *
	 * @return void
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetBaseMapping_TooHigh()
	{
		ApiCodeBase::getBaseMapping(ApiCodeBase::RESERVED_MAX_API_CODE + 1);
	}

	/**
	 * Tests getBaseMapping()
	 *
	 * @return void
	 */
	public function testGetBaseMapping()
	{
		// check how mapped code handling works
		$mapping = ApiCodeBase::getBaseMapping(ApiCodeBase::OK);
		$this->assertNotNull($mapping);

		// check how not-mapped code is handled
		$base_map = $this->getProtectedMember(ApiCodeBase::class, 'base_map');

		for ($code = ApiCodeBase::RESERVED_MIN_API_CODE; $code < ApiCodeBase::RESERVED_MAX_API_CODE; $code++) {
			if (!array_key_exists($code, $base_map)) {
				break;
			}
		}

		$mapping = ApiCodeBase::getBaseMapping($code);
		$this->assertNull($mapping);
	}

}
