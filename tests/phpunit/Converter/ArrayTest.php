<?php
/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Converter;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2025 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use MarcinOrlowski\PhpunitExtraAsserts\ExtraAsserts;
use MarcinOrlowski\PhpunitExtraAsserts\Generator;
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\Converter\Converters\FakeConverter;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModel;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;
use MarcinOrlowski\ResponseBuilder\Type;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Class ArrayTest
 */
class ArrayTest extends TestCase
{
    /**
     * Tests how we convert array of objects
     */
    public function testConvertArrayOfObjects(): void
    {
        // GIVEN model object with randomly set member value
        $model_1 = new TestModel(Generator::getRandomString('model_1'));
        $model_2 = new TestModel(Generator::getRandomString('model_2'));
        $model_3 = null;

        $model_key = Generator::getRandomString('conv_key');

        $data = [
            $model_1,
            $model_2,
            $model_3,
        ];

        // AND having its class configured for auto conversion
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, [
            \get_class($model_1) => [
                RB::KEY_KEY     => $model_key,
                RB::KEY_HANDLER => ToArrayConverter::class,
            ],
        ]);

        // WHEN this object is returned
        $converted = (new Converter())->convert($data);

        /** @var array<string, mixed> $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_PRIMITIVES) ?? [];
        ExtraAsserts::assertIsArray($cfg);
        $this->assertNotEmpty($cfg);
        /** @var array<string, mixed> $array_config */
        $array_config = $cfg[ Type::ARRAY ];
        $key = $array_config[ RB::KEY_KEY ];
        /** @var string $key */

        ExtraAsserts::assertIsArray($converted);
        /** @var array<string, mixed> $converted */
        $this->assertCount(1, $converted);
        $this->assertArrayHasKey($key, $converted);
        $converted = $converted[ $key ];
        /** @var array<int, mixed> $converted */
        $this->assertCount(\count($data), $converted);

        $this->assertCount(\count($data), $converted);

        /** @var array<string, mixed> $item0 */
        $item0 = $converted[0];
        $this->assertValidConvertedTestClass($model_1, $item0);
        /** @var array<string, mixed> $item1 */
        $item1 = $converted[1];
        $this->assertValidConvertedTestClass($model_2, $item1);
        $this->assertIsNotBool($converted[2]);
        $this->assertNull($converted[2]);
    }

    /**
     * Tests how we convert array of nested arrays of objects
     */
    public function testConvertArrayOfArraysOfObjects(): void
    {
        // GIVEN model object with randomly set member value
        $model_1 = new TestModel(Generator::getRandomString('model_1'));
        $model_2 = new TestModel(Generator::getRandomString('model_2'));

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
        $model_key = Generator::getRandomString();
        $model_class = \get_class($model_1);
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, [
            $model_class => [
                RB::KEY_KEY     => $model_key,
                RB::KEY_HANDLER => ToArrayConverter::class,
            ],
        ]);

        // WHEN this object is returned
        $converted = (new Converter())->convert($data);

        /** @var array<string, mixed> $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_PRIMITIVES) ?? [];
        ExtraAsserts::assertIsArray($cfg);
        $this->assertNotEmpty($cfg);
        /** @var array<string, mixed> $array_config */
        $array_config = $cfg[ Type::ARRAY ];
        $key = $array_config[ RB::KEY_KEY ];

        ExtraAsserts::assertIsArray($converted);
        /** @var array<string, mixed> $converted */
        $this->assertCount(1, $converted);
        /** @var string $key */
        $this->assertArrayHasKey($key, $converted);
        $converted = $converted[ $key ];
        /** @var array<int, array<int, mixed>> $converted */
        $this->assertCount(\count($data), $converted);

        foreach ($converted as $row) {
            ExtraAsserts::assertIsArray($row);
            /** @var array<string, mixed> $item0 */
            $item0 = $row[0];
            /** @var array<string, mixed> $item1 */
            $item1 = $row[1];
            $this->assertValidConvertedTestClass($model_1, $item0);
            $this->assertValidConvertedTestClass($model_2, $item1);
        }
    }

    /**
     * Tests if exception is thrown for invalid mixed-key array
     *
     * @param array<int|string, mixed> $data
     */
    #[DataProvider('convertArrayOfKeyAndKeylessItemsProvider')]
    public function testConvertArrayOfKeyAndKeylessItems(array $data): void
    {
        // GIVEN $data array with mixed keys (int/string and string/int order)
        // Either all items have user provided keys, or none.
        // Mixed arrays are not supported by design.

        // AND having its class configured for auto conversion
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, [
            TestModel::class => [
                RB::KEY_KEY     => 'XXX',
                RB::KEY_HANDLER => ToArrayConverter::class,
            ],
        ]);

        // WHEN conversion is attempted, exception should be thrown
        $this->expectException(Ex\ArrayWithMixedKeysException::class);

        (new Converter())->convert($data);
    }

    /**
     * Data provider for testConvertArrayOfKeyAndKeylessItems
     *
     * @return array<int, array<int, array<int|string, mixed>>>
     */
    public static function convertArrayOfKeyAndKeylessItemsProvider(): array
    {
        // GIVEN model object with randomly set member value
        $model_1 = new TestModel(Generator::getRandomString('model_1'));
        $model_1_key = Generator::getRandomString('model_1_key');
        $model_2 = new TestModel(Generator::getRandomString('model_2'));

        // string/int order
        $data1 = [
            $model_1_key => $model_1,
            $model_2,
        ];

        // int/string order
        $data2 = [
            $model_2,
            $model_1_key => $model_1,
        ];

        return [
            [$data1],
            [$data2],
        ];
    }

    /**
     * Tests handling of mix of objects and keyed arrays of objeccts
     */
    public function testConvertArrayNestedWithKeyedItems(): void
    {
        // GIVEN model object with randomly set member value
        $model_1 = new TestModel(Generator::getRandomString('model_1'));
        $item1_key = Generator::getRandomString('model_1_key');
        $model_2 = new TestModel(Generator::getRandomString('model_2'));
        $item2_key = Generator::getRandomString('model_2_key');
        $item3_key = Generator::getRandomString('item_3_key');
        $model_4 = new TestModel(Generator::getRandomString('model_4'));
        $item4_key = Generator::getRandomString('model_4_key');
        $model_5 = new TestModel(Generator::getRandomString('model_5'));
        $item5_key = Generator::getRandomString('model_5_key');

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
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, [
            \get_class($model_1) => [
                RB::KEY_KEY     => 'XXX',
                RB::KEY_HANDLER => ToArrayConverter::class,
            ],
        ]);

        // WHEN this object is returned
        $converted = (new Converter())->convert($data);

        ExtraAsserts::assertIsArray($converted);
        /** @var array<string, mixed> $converted */
        $this->assertCount(\count($data), $converted);
        $this->assertArrayHasKey($item3_key, $converted);
        $nested = $data[ $item3_key ];
        ExtraAsserts::assertIsArray($nested);
        /** @var non-empty-array<string, TestModel> $nested */
        /** @var array<string, mixed> $nested_converted */
        $nested_converted = $converted[ $item3_key ];
        $this->assertCount(\count($nested), $nested_converted);

        /** @var array<string, mixed> $item1_data */
        $item1_data = $converted[ $item1_key ];
        $this->assertEquals($model_1->getVal(), $item1_data[ TestModel::FIELD_NAME ]);
        /** @var array<string, mixed> $item2_data */
        $item2_data = $converted[ $item2_key ];
        $this->assertEquals($model_2->getVal(), $item2_data[ TestModel::FIELD_NAME ]);
        /** @var array<string, mixed> $item4_data */
        $item4_data = $nested_converted[ $item4_key ];
        $this->assertEquals($model_4->getVal(), $item4_data[ TestModel::FIELD_NAME ]);
        /** @var array<string, mixed> $item5_data */
        $item5_data = $nested_converted[ $item5_key ];
        $this->assertEquals($model_5->getVal(), $item5_data[ TestModel::FIELD_NAME ]);
    }

    /**
     * Checks if convert returns data merged properly if convert related setings
     * feature no "key" element.
     */
    public function testConvertWithNoKeyInConfig(): void
    {
        $model_1 = new TestModel(Generator::getRandomString('model_1'));

        // AND having its class configured for auto conversion
        $key = Generator::getRandomString('key');
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, [
            \get_class($model_1) => [
                RB::KEY_HANDLER => ToArrayConverter::class,
                RB::KEY_KEY     => $key,
            ],
        ]);

        $result = (new Converter())->convert($model_1);

        ExtraAsserts::assertIsArray($result);
        /** @var array<string, mixed> $result */
        $this->assertArrayHasKey($key, $result);
        /** @var array<string, mixed> $key_data */
        $key_data = $result[ $key ];
        $this->assertCount(1, $key_data);
        $this->assertArrayHasKey(TestModel::FIELD_NAME, $key_data);
        $this->assertEquals($model_1->getVal(), $key_data[ TestModel::FIELD_NAME ]);
    }

    /**
     * Checks if convert returns data merged properly if convert related setings
     * feature no "key" element.
     */
    public function testConvertMultipleWithNoKeyInConfig(): void
    {
        $model_1 = new TestModel(Generator::getRandomString('model_1'));
        $model_2 = new TestModel(Generator::getRandomString('model_2'));

        $data = [
            $model_1,
            $model_2,
        ];

        // AND having its class configured for auto conversion
        $model_key = Generator::getRandomString();
        $model_class = \get_class($model_1);
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, [
            $model_class => [
                RB::KEY_HANDLER => ToArrayConverter::class,
                RB::KEY_KEY     => $model_key,
            ],
        ]);

        /** @var array<string, mixed> $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_PRIMITIVES) ?? [];
        ExtraAsserts::assertIsArray($cfg);
        $this->assertNotEmpty($cfg);
        /** @var array<string, mixed> $array_config */
        $array_config = $cfg[ Type::ARRAY ];
        $key = $array_config[ RB::KEY_KEY ];

        $result = (new Converter())->convert($data);
        ExtraAsserts::assertIsArray($result);
        /** @var array<string, mixed> $result */
        $this->assertCount(1, $result);
        /** @var array<int, mixed> $result_data */
        $result_data = $result[ $key ];
        $this->assertCount(\count($data), $result_data);

        /** @var array<string, mixed> $item0 */
        $item0 = $result_data[0];
        $this->assertArrayHasKey(TestModel::FIELD_NAME, $item0);
        $this->assertEquals($model_1->getVal(), $item0[ TestModel::FIELD_NAME ]);
        /** @var array<string, mixed> $item1 */
        $item1 = $result_data[1];
        $this->assertArrayHasKey(TestModel::FIELD_NAME, $item1);
        $this->assertEquals($model_2->getVal(), $item1[ TestModel::FIELD_NAME ]);
    }

    /**
     * Checks if converter config can be completely overriden by the user config.
     */
    public function testConvertWithOverridenDefaultConfig(): void
    {
        // GIVEN built-in converter config
        /** @var array<string, mixed> $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES) ?? [];
        ExtraAsserts::assertIsArray($cfg);
        $this->assertNotEmpty($cfg);

        // HAVING custom converter set to replace built-in settings
        $fake = new FakeConverter();

        $key = Generator::getRandomString();
        /** @var array<string, mixed> $collection_config */
        $collection_config = $cfg[ Collection::class ];
        $collection_config[ RB::KEY_HANDLER ] = \get_class($fake);
        $collection_config[ RB::KEY_KEY ] = $key;
        $cfg[ Collection::class ] = $collection_config;
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, $cfg);

        // WHEN converting the data, we expect FakeConverter to be used
        $data = collect([1,
                         2,
                         3,
        ]);

        $result = (new Converter())->convert($data);

        ExtraAsserts::assertIsArray($result);
        /** @var array<string, mixed> $result */
        $this->assertArrayHasKey($key, $result);
        /** @var array<string, mixed> $result_data */
        $result_data = $result[ $key ];
        $this->assertCount(1, $result_data);
        $this->assertArrayHasKey($fake->key, $result_data);
        $this->assertEquals($result_data[ $fake->key ], $fake->val);
    }

    // ---------------------------------------------------------------------------------------------

    /**
     * Helper method that validates converted element with values from
     * source object.
     *
     * @param TestModel $obj  Source object converted.
     * @param array<string, mixed> $item Result of the conversion.
     */
    protected function assertValidConvertedTestClass(TestModel $obj, array $item): void
    {
        $this->assertArrayHasKey(TestModel::FIELD_NAME, $item);
        $this->assertEquals($obj->getVal(), $item[ TestModel::FIELD_NAME ]);
    }

} // end of class
