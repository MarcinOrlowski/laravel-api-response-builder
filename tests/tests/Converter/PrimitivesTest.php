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

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModel;

class PrimitivesTest extends TestCase
{
	/**
	 * Checks if getPrimitivesMapping would throw exception on invalid configuration data
	 */
	public function testGetPrimitivesMapping_InvalidConfigurationData(): void
	{
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER_PRIMITIVES, 'invalid');

		$this->expectException(\RuntimeException::class);

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callProtectedMethod(Converter::class, 'getPrimitivesMapping');
	}

	/**
	 * Checks if getPrimitivesMapping would return empty array if there's no "primitives" config entry
	 */
	public function testGetPrimitivesMapping_NoMappingConfig(): void
	{
		// Remove any classes config
		/** @noinspection PhpUndefinedMethodInspection */
		Config::offsetUnset(ResponseBuilder::CONF_KEY_CONVERTER_PRIMITIVES);

		/** @noinspection PhpUnhandledExceptionInspection */
		$result = $this->callProtectedMethod(Converter::class, 'getPrimitivesMapping');
		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	/**
	 * Checks if getPrimitiveMappingConfigOrThrow() throws exception when config for given primitive type is of invalid type.
	 */
	public function testGetPrimitiveMappingConfigOrThrow_NoConfig(): void
	{
		Config::offsetUnset(ResponseBuilder::CONF_KEY_CONVERTER_PRIMITIVES . '.' . ResponseBuilder::PRIMITIVE_BOOLEAN);

		// getPrimitiveMapping is called by constructor.
		$this->expectException(\InvalidArgumentException::class);
		new Converter();
	}

	/**
	 * Checks if getPrimitiveMappingConfigOrThrow() throws exception when config for given primitive type lacks mandatory keys
	 */
	public function testGetPrimitiveMappingConfigOrThrow_ConfigInvalidType(): void
	{
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER_PRIMITIVES . '.' . ResponseBuilder::PRIMITIVE_BOOLEAN, []);

		// getPrimitiveMapping is called by constructor.
		$this->expectException(\RuntimeException::class);
		new Converter();
	}

	// -----------------------------------------------------------------------------------------------------------

	/**
	 * Checks how we convert directly passed object
	 */
	public function testDirectObject(): void
	{
		$model_val = $this->getRandomString();
		$model = new TestModel($model_val);

		// AND having its class configured for auto conversion
		$key = $this->getRandomString('key');
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER_CLASSES, [
			\get_class($model) => [
				ResponseBuilder::KEY_HANDLER => ToArrayConverter::class,
				ResponseBuilder::KEY_KEY     => $key,
			],
		]);

		// WHEN this object is returned
		$converted = (new Converter())->convert($model);

		// THEN we expect returned data to be converted and use KEY_KEY element.
		$this->assertIsArray($converted);
		$this->assertArrayHasKey($key, $converted);
		$this->assertCount(1, $converted[ $key ]);
		$this->assertEquals($model_val, $converted[ $key ]['val']);
	}

	/**
	 * Checks if passing boolean as direct payload works as expected.
	 */
	public function testDirectBool(): void
	{
		// GIVEN primitive value
		$value = mt_rand(0, 1) ? false : true;
		$this->doDirectPrimitiveTest($value);
	}

	/**
	 * Checks if passing double as direct payload works as expected.
	 */
	public function testDirectDouble(): void
	{
		// GIVEN primitive value
		$value = ((double)mt_rand(0, 100000) / mt_rand(0, 1000)) + 0.1;
		$this->doDirectPrimitiveTest($value);
	}

	/**
	 * Checks if passing integer as direct payload works as expected.
	 */
	public function testDirectInteger(): void
	{
		// GIVEN primitive value
		$value = mt_rand(0, 10000);
		$this->doDirectPrimitiveTest($value);
	}

	/**
	 * Checks if passing string as direct payload works as expected.
	 */
	public function testDirectString(): void
	{
		// GIVEN primitive value
		$value = $this->getRandomString();
		$this->doDirectPrimitiveTest($value);
	}

	/**
	 * Helper method to perform some common tests for primitive as direct payload.
	 *
	 * @param mixed $value
	 *
	 * @throws \ReflectionException
	 */
	protected function doDirectPrimitiveTest($value): void
	{
		// GIVEN primitive value $value

		// WHEN passing it as direct payaload
		$converter = new Converter();
		$converted = $converter->convert($value);

		// THEN we expect returned data to be keyed as per primitive's configuration.
		$this->assertIsArray($converted);

		/** @noinspection PhpUnhandledExceptionInspection */
		$cfg = $this->callProtectedMethod($converter, 'getPrimitiveMappingConfigOrThrow', [\gettype($value)]);
		$this->assertIsArray($cfg);
		$this->assertNotEmpty($cfg);
		$key = $cfg[ ResponseBuilder::KEY_KEY ];
		$this->assertArrayHasKey($key, $converted);
		$this->assertEquals($value, $converted[ $key ]);
	}

}
