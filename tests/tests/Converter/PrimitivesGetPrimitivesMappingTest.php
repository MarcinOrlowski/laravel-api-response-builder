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

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModel;
use MarcinOrlowski\ResponseBuilder\Type;

class PrimitivesGetPrimitivesMappingTest extends TestCase
{
	/**
	 * Checks if getPrimitivesMapping would throw exception on invalid configuration data
	 */
	public function testGetPrimitivesMappingInvalidConfigurationData(): void
	{
		Config::set(RB::CONF_KEY_CONVERTER_PRIMITIVES, 'invalid');

		$this->expectException(Ex\InvalidConfigurationException::class);

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callProtectedMethod(Converter::class, 'getPrimitivesMapping');
	}

	/**
	 * Checks if getPrimitivesMapping would return empty array if there's no "primitives" config entry
	 */
	public function testGetPrimitivesMappingNoMappingConfig(): void
	{
		// Remove any classes config
		/** @noinspection PhpUndefinedMethodInspection */
		Config::offsetUnset(RB::CONF_KEY_CONVERTER_PRIMITIVES);

		/** @noinspection PhpUnhandledExceptionInspection */
		$result = $this->callProtectedMethod(Converter::class, 'getPrimitivesMapping');
		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	/**
	 * Checks if getPrimitiveMappingConfigOrThrow() throws exception when config for given
	 * primitive type is of invalid type.
	 */
	public function testGetPrimitiveMappingConfigOrThrow_NoConfigKeys(): void
	{
		Config::offsetUnset(RB::CONF_KEY_CONVERTER_PRIMITIVES . '.' . Type::BOOLEAN);

		// getPrimitiveMapping is called by constructor.
		$this->expectException(Ex\InvalidConfigurationElementException::class);
		new Converter();
	}

	/**
	 * Checks if getPrimitiveMappingConfigOrThrow() throws exception when config for given primitive type lacks mandatory keys
	 */
	public function testGetPrimitiveMappingConfigOrThrow_ConfigInvalidType(): void
	{
		Config::set(RB::CONF_KEY_CONVERTER_PRIMITIVES . '.' . Type::BOOLEAN, []);

		// getPrimitiveMapping() is called by constructor.
		$this->expectException(Ex\IncompleteConfigurationException::class);
		new Converter();
	}

	public function testGetPrimitiveMappingConfigOrThrow_NoConfig(): void
	{
		$cfg = Config::get(RB::CONF_KEY_CONVERTER_PRIMITIVES);
		unset($cfg[ Type::BOOLEAN ]);
		Config::set(RB::CONF_KEY_CONVERTER_PRIMITIVES, $cfg);

		// getPrimitiveMapping is called by constructor.
		$this->expectException(Ex\ConfigurationNotFoundException::class);
		$converter = new Converter();
		$converter->convert(false);

	}

}
