<?php /** @noinspection PhpUndefinedClassInspection */

namespace MarcinOrlowski\ResponseBuilder\Tests;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApiCodeBaseTest extends TestCase
{
	/**
	 * Tests getMinCode() with invalid config
	 */
	public function testGetMinCodeMissingConfigKey(): void
	{
		$this->expectException(\RuntimeException::class);

		/** @noinspection PhpUndefinedClassInspection */
		\Config::offsetUnset(ResponseBuilder::CONF_KEY_MIN_CODE);
		BaseApiCodes::getMinCode();
	}

	/**
	 * Tests getMaxCode() with invalid config
	 */
	public function testGetMaxCodeMissingConfigKey(): void
	{
		$this->expectException(\RuntimeException::class);

		/** @noinspection PhpUndefinedClassInspection */
		\Config::offsetUnset(ResponseBuilder::CONF_KEY_MAX_CODE);
		BaseApiCodes::getMaxCode();
	}

	/**
	 * Tests getMap() with missing config
	 */
	public function testGetMapMissingConfigKey(): void
	{
		$this->expectException(\RuntimeException::class);

		/** @noinspection PhpUndefinedClassInspection */
		\Config::offsetUnset(ResponseBuilder::CONF_KEY_MAP);
		BaseApiCodes::getMap();
	}

	/**
	 * Tests getMap() with wrong config
	 */
	public function testGetMapWrongConfig(): void
	{
		$this->expectException(\RuntimeException::class);

		/** @noinspection PhpUndefinedClassInspection */
		\Config::set(ResponseBuilder::CONF_KEY_MAP, false);
		BaseApiCodes::getMap();
	}
}
