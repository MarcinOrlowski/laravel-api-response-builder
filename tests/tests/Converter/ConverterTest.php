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
use MarcinOrlowski\ResponseBuilder\Tests\Converters\FakeConverter;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModel;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModelChild;

class ConverterTest extends TestCase
{
	/**
	 * Checks if Converter's constructor would throw exception when configuration is invalid.
	 */
	public function testConstructor(): void
	{
		// GIVEN incorrect mapping configuration
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER, false);

		// THEN we expect exception thrown
		$this->expectException(\RuntimeException::class);

		// WHEN attempt to instantiate Converter class
		new Converter();
	}

	/**
	 * Checks if object of child class will be properly converted when
	 * configuration mapping exists for its parent class only.
	 */
	public function testSubclassOfConfiguredClassConversion(): void
	{
		// GIVEN two objects with direct inheritance relation
		$parent_val = $this->getRandomString('parent');
		$parent = new TestModel($parent_val);
		$child_val = $this->getRandomString('child');
		$child = new TestModelChild($child_val);

		// HAVING indirect mapping configuration (of parent class)
		$key = $this->getRandomString('key');
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER, [
			\get_class($parent) => [
				ResponseBuilder::KEY_HANDLER => ToArrayConverter::class,
				ResponseBuilder::KEY_KEY => $key,
			],
		]);

		// WHEN we try to pass of child class
		$result = (new Converter())->convert($child);

		// EXPECT it to be converted as per parent class configuration entry
		$this->assertIsArray($result);
		$this->assertArrayHasKey($key, $result);
		$result = $result[$key];
		$this->assertCount(1, $result);
		$this->assertEquals($child_val, $result['val']);
	}

	/**
	 * Checks if getClassesMapping would throw exception on invalid configuration data
	 */
	public function testGetClassesMapping_InvalidConfigurationData(): void
	{
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER, 'invalid');

		$this->expectException(\RuntimeException::class);

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callProtectedMethod(Converter::class, 'getClassesMapping');
	}

	/**
	 * Checks if getClassesMapping would return empty array if there's no "classes" config entry
	 */
	public function testGetClassesMapping_NoMappingConfig(): void
	{
		// Remove any classes config
		/** @noinspection PhpUndefinedMethodInspection */
		Config::offsetUnset(ResponseBuilder::CONF_KEY_CONVERTER);

		/** @noinspection PhpUnhandledExceptionInspection */
		$result = $this->callProtectedMethod(Converter::class, 'getClassesMapping');
		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	/**
	 * Checks how we convert directly passed object
	 */
	public function testConvert_DirectObject(): void
	{
		$model_val = $this->getRandomString();
		$model = new TestModel($model_val);

		// AND having its class configured for auto conversion
		$key = $this->getRandomString('key');
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER, [
			\get_class($model) => [
				ResponseBuilder::KEY_HANDLER => ToArrayConverter::class,
				ResponseBuilder::KEY_KEY => $key,
			],
		]);

		// WHEN this object is returned
		$converted = (new Converter())->convert($model);

		// THEN we expect returned data to be converted and use KEY_KEY element.
		$this->assertIsArray($converted);
		$this->assertArrayHasKey($key, $converted);
		$this->assertCount(1, $converted[$key]);
		$this->assertEquals($model_val, $converted[$key]['val']);
	}

	/**
	 * Tests how we convert array of objects
	 */
	public function testConvert_ArrayOfObjects(): void
	{
		// GIVEN model object with randomly set member value
		$model_1 = new TestModel($this->getRandomString('model_1'));
		$model_2 = new TestModel($this->getRandomString('model_2'));
		$model_3 = null;

		$model_key = $this->getRandomString('conv_key');

		$data = [
			$model_1,
			$model_2,
			$model_3,
		];

		// AND having its class configured for auto conversion
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER, [
			\get_class($model_1) => [
				ResponseBuilder::KEY_KEY     => $model_key,
				ResponseBuilder::KEY_HANDLER => ToArrayConverter::class,
			],
		]);

		// WHEN this object is returned
		$converted = (new Converter())->convert($data);

		// FIXME Illuminate\Contracts\Support\Arrayable seems to be mandatory now in config, which
		// is bad. we need to have some way to handle array as direct payload with empty config
		// ** MULTIPLE OCCURENCES - search for \Arrayable::class in tests
		$cfg = Config::get(ResponseBuilder::CONF_KEY_CONVERTER);
		$key = $cfg[ \Illuminate\Contracts\Support\Arrayable::class ][ ResponseBuilder::KEY_KEY ];

		$this->assertIsArray($converted);
		$this->assertCount(1, $converted);
		$this->assertArrayHasKey($key, $converted);
		$converted = $converted[$key];
		$this->assertCount(\count($data), $converted);

		$this->assertCount(\count($data), $converted);

		$this->assertValidConvertedTestClass($model_1, $converted[0]);
		$this->assertValidConvertedTestClass($model_2, $converted[1]);
		$this->assertIsNotBool($converted[2]);
		$this->assertNull($converted[2]);
	}

	/**
	 * Tests how we convert array of nested arrays of objects
	 */
	public function testConvert_ArrayOfArraysOfObjects(): void
	{
		// GIVEN model object with randomly set member value
		$model_1 = new TestModel($this->getRandomString('model_1'));
		$model_2 = new TestModel($this->getRandomString('model_2'));

		$data = [
			[
				$model_1,
				$model_2,
			],
			[
				$model_1,
				$model_2,
			],
		];
		// AND having its class configured for auto conversion
		$model_key = $this->getRandomString();
		$model_class = \get_class($model_1);
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER, [
			$model_class => [
				ResponseBuilder::KEY_KEY     => $model_key,
				ResponseBuilder::KEY_HANDLER => ToArrayConverter::class,
			],
		]);

		// WHEN this object is returned
		$converted = (new Converter())->convert($data);

		// FIXME Illuminate\Contracts\Support\Arrayable seems to be mandatory now in config, which
		// is bad. we need to have some way to handle array as direct payload with empty config
		// ** MULTIPLE OCCURENCES - search for \Arrayable::class in tests
		$cfg = Config::get(ResponseBuilder::CONF_KEY_CONVERTER);
		$key = $cfg[ \Illuminate\Contracts\Support\Arrayable::class ][ ResponseBuilder::KEY_KEY ];

		$this->assertIsArray($converted);
		$this->assertCount(1, $converted);
		$this->assertArrayHasKey($key, $converted);
		$converted = $converted[$key];
		$this->assertCount(\count($data), $converted);

		foreach ($converted as $row) {
			$this->assertIsArray($row);
			$this->assertValidConvertedTestClass($model_1, $row[0]);
			$this->assertValidConvertedTestClass($model_2, $row[1]);
		}
	}

	/**
	 * Tests if exception is thrown for invalid mixed-key array
	 */
	public function testConvert_ArrayOfKeyAndKeylessItems(): void
	{
		// GIVEN model object with randomly set member value
		$model_1 = new TestModel($this->getRandomString('model_1'));
		$model_1_key = $this->getRandomString('model_1_key');
		$model_2 = new TestModel($this->getRandomString('model_2'));

		// Either all items have user provided keys, or none.
		// Mixed arrays are not supported by design.
		$data = [
			$model_1_key => $model_1,
			$model_2,
		];

		// AND having its class configured for auto conversion
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER, [
			\get_class($model_1) => [
				ResponseBuilder::KEY_KEY     => 'XXX',
				ResponseBuilder::KEY_HANDLER => ToArrayConverter::class,
			],
		]);

		// WHEN conversion is attempted, exception should be thrown
		$this->expectException(\RuntimeException::class);

		(new Converter())->convert($data);
	}

	/**
	 * Tests handling of mix of objects and keyed arrays of objeccts
	 */
	public function testConvert_ArrayNestedWithKeyedItems(): void
	{
		// GIVEN model object with randomly set member value
		$model_1 = new TestModel($this->getRandomString('model_1'));
		$item1_key = $this->getRandomString('model_1_key');
		$model_2 = new TestModel($this->getRandomString('model_2'));
		$item2_key = $this->getRandomString('model_2_key');
		$item3_key = $this->getRandomString('item_3_key');
		$model_4 = new TestModel($this->getRandomString('model_4'));
		$item4_key = $this->getRandomString('model_4_key');
		$model_5 = new TestModel($this->getRandomString('model_5'));
		$item5_key = $this->getRandomString('model_5_key');

		// Either all items have user provided keys, or none.
		// Mixed arrays are not supported by design.
		$data = [
			$item1_key => $model_1,
			$item2_key => $model_2,
			$item3_key => [
				$item4_key => $model_4,
				$item5_key => $model_5,
			],
		];

		// AND having its class configured for auto conversion
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER, [
			\get_class($model_1) => [
				ResponseBuilder::KEY_KEY     => 'XXX',
				ResponseBuilder::KEY_HANDLER => ToArrayConverter::class,
			],
		]);

		// WHEN this object is returned
		$converted = (new Converter())->convert($data);

		$this->assertIsArray($converted);
		$this->assertCount(\count($data), $converted);
		$this->assertArrayHasKey($item3_key, $converted);
		$this->assertCount(\count($data[ $item3_key ]), $converted[ $item3_key ]);

		$this->assertEquals($model_1->getVal(), $converted[ $item1_key ]['val']);
		$this->assertEquals($model_2->getVal(), $converted[ $item2_key ]['val']);
		$this->assertEquals($model_4->getVal(), $converted[ $item3_key ][ $item4_key ]['val']);
		$this->assertEquals($model_5->getVal(), $converted[ $item3_key ][ $item5_key ]['val']);
	}

	/**
	 * Checks if convert() throws exception when fed with invalid type of data.
	 */
	public function testConvertWithInvalidDataType(): void
	{
		// Bool is not a valid type of data to be converted
		$data = false;

		$this->expectException(\InvalidArgumentException::class);
		(new Converter())->convert($data);
	}

	/**
	 * Checks if convert returns data merged properly if convert related setings
	 * feature no "key" element.
	 */
	public function testConvertWithNoKeyInConfig(): void
	{
		$model_1 = new TestModel($this->getRandomString('model_1'));

		// AND having its class configured for auto conversion
		$key = $this->getRandomString('key');
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER, [
			\get_class($model_1) => [
				ResponseBuilder::KEY_HANDLER => ToArrayConverter::class,
				ResponseBuilder::KEY_KEY => $key,
			],
		]);

		$result = (new Converter())->convert($model_1);

		$this->assertIsArray($result);
		$this->assertArrayHasKey($key, $result);
		$this->assertCount(1, $result[$key]);
		$this->assertArrayHasKey('val', $result[$key]);
		$this->assertEquals($model_1->getVal(), $result[$key]['val']);
	}

	/**
	 * Checks if convert returns data merged properly if convert related setings
	 * feature no "key" element.
	 */
	public function testConvertMultipleWithNoKeyInConfig(): void
	{
		$model_1 = new TestModel($this->getRandomString('model_1'));
		$model_2 = new TestModel($this->getRandomString('model_2'));

		$data = [
			$model_1,
			$model_2,
		];

		// AND having its class configured for auto conversion
		$model_key = $this->getRandomString();
		$model_class = \get_class($model_1);
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER, [
			$model_class => [
				ResponseBuilder::KEY_HANDLER => ToArrayConverter::class,
				ResponseBuilder::KEY_KEY => $model_key,
			],
		]);

		// FIXME Illuminate\Contracts\Support\Arrayable seems to be mandatory now in config, which
		// is bad. we need to have some way to handle array as direct payload with empty config
		// ** MULTIPLE OCCURENCES - search for \Arrayable::class in tests
		$cfg = Config::get(ResponseBuilder::CONF_KEY_CONVERTER);
		$key = $cfg[ \Illuminate\Contracts\Support\Arrayable::class ][ ResponseBuilder::KEY_KEY ];

		$result = (new Converter())->convert($data);
		$this->assertIsArray($result);
		$this->assertCount(1, $result);
		$result = $result[$key];
		$this->assertCount(\count($data), $result);

		$this->assertArrayHasKey('val', $result[0]);
		$this->assertEquals($model_1->getVal(), $result[0]['val']);
		$this->assertArrayHasKey('val', $result[1]);
		$this->assertEquals($model_2->getVal(), $result[1]['val']);
	}

	/**
	 * Checks if converter config can be completely overriden by the user config.
	 */
	public function testConvertWithOverridenDefaultConfig(): void
	{
		// GIVEN built-in converter config
		$cfg = Config::get(ResponseBuilder::CONF_KEY_CONVERTER);
		$this->assertIsArray($cfg);
		$this->assertNotEmpty($cfg);

		// HAVING custom converter set to replace built-in settings
		$fake = new FakeConverter();

		$key = $this->getRandomString();
		$cfg[ Collection::class ][ ResponseBuilder::KEY_HANDLER ] = \get_class($fake);
		$cfg[ Collection::class ][ ResponseBuilder::KEY_KEY ] = $key;
		Config::set(ResponseBuilder::CONF_KEY_CONVERTER, $cfg);

		// WHEN converting the data, we expect FakeConverter to be used
		$data = collect([1,
		                 2,
		                 3]);

		$result = (new Converter())->convert($data);

		$this->assertIsArray($result);
		$this->assertArrayHasKey($key, $result);
		$result = $result[$key];
		$this->assertCount(1, $result);
		$this->assertArrayHasKey($fake->key, $result);
		$this->assertEquals($result[ $fake->key ], $fake->val);
	}

	// -----------------------------------------------------------------------------------------------------------

	/**
	 * Helper method that validates converted element with values from
	 * source object.
	 *
	 * @param TestModel $obj  Source object converted.
	 * @param array     $item Result of the conversion.
	 */
	protected function assertValidConvertedTestClass(TestModel $obj, array $item): void
	{
		$this->assertArrayHasKey('val', $item);
		$this->assertEquals($obj->getVal(), $item['val']);
	}
}
