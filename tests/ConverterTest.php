<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

/** @noinspection PhpMultipleClassesDeclarationsInOneFile */

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

use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ConverterTest extends TestCase
{
	/**
	 * Checks if getClassesMapping would throw exception on invalid configuration data
	 */
	public function testGetClassesMapping_InvalidConfigurationData(): void
	{
		Config::set(ResponseBuilder::CONF_KEY_CLASSES, 'invalid');

		$this->expectException(\RuntimeException::class);

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callProtectedMethod(ResponseBuilder::class, 'getClassesMapping');
	}

	/**
	 * Checks if getClassesMapping would return empty array if there's no "classes" config entry
	 */
	public function testGetClassesMapping_NoMappingConfig(): void
	{
		// remove any classes config
		/** @noinspection PhpUndefinedMethodInspection */
		Config::offsetUnset(ResponseBuilder::CONF_KEY_CLASSES);

		/** @noinspection PhpUnhandledExceptionInspection */
		$result = $this->callProtectedMethod(ResponseBuilder::class, 'getClassesMapping');
		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}
}
