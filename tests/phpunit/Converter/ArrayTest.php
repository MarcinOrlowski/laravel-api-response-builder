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
 * @copyright 2016-2024 Marcin Orlowski
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

        /** @var array $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_PRIMITIVES) ?? [];
        ExtraAsserts::assertIsArray($cfg);
        $this->assertNotEmpty($cfg);
        $key = $cfg[ Type::ARRAY ][ RB::KEY_KEY ];
        /** @var string $key */

        ExtraAsserts::assertIsArray($converted);
        /** @var array $converted */
        $this->assertCount(1, $converted);
        $this->assertArrayHasKey($key, $converted);
        $converted = $converted[ $key ];
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

        /** @var array $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_PRIMITIVES) ?? [];
        ExtraAsserts::assertIsArray($cfg);
        $this->assertNotEmpty($cfg);
        $key = $cfg[ Type::ARRAY ][ RB::KEY_KEY ];

        ExtraAsserts::assertIsArray($converted);
        /** @var array $converted */
        $this->assertCount(1, $converted);
        /** @var string $key */
        $this->assertArrayHasKey($key, $converted);
        $converted = $converted[ $key ];
        $this->assertCount(\count($data), $converted);

        foreach ($converted as $row) {
            ExtraAsserts::assertIsArray($row);
            $this->assertValidConvertedTestClass($model_1, $row[0]);
            $this->assertValidConvertedTestClass($model_2, $row[1]);
        }
    }

    /**
     * Tests if exception is thrown for invalid mixed-key array
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
        /** @var array $converted */
        $this->assertCount(\count($data), $converted);
        $this->assertArrayHasKey($item3_key, $converted);
        $nested = $data[ $item3_key ];
        ExtraAsserts::assertIsArray($nested);
        /** @var array $nested */
        $this->assertCount(\count($nested), $converted[ $item3_key ]);

        $this->assertEquals($model_1->getVal(), $converted[ $item1_key ][ TestModel::FIELD_NAME ]);
        $this->assertEquals($model_2->getVal(), $converted[ $item2_key ][ TestModel::FIELD_NAME ]);
        $this->assertEquals($model_4->getVal(), $converted[ $item3_key ][ $item4_key ][ TestModel::FIELD_NAME ]);
        $this->assertEquals($model_5->getVal(), $converted[ $item3_key ][ $item5_key ][ TestModel::FIELD_NAME ]);
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
        /** @var array $result */
        $this->assertArrayHasKey($key, $result);
        $this->assertCount(1, $result[ $key ]);
        $this->assertArrayHasKey(TestModel::FIELD_NAME, $result[ $key ]);
        $this->assertEquals($model_1->getVal(), $result[ $key ][ TestModel::FIELD_NAME ]);
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

        /** @var array $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_PRIMITIVES) ?? [];
        ExtraAsserts::assertIsArray($cfg);
        $this->assertNotEmpty($cfg);
        $key = $cfg[ Type::ARRAY ][ RB::KEY_KEY ];

        $result = (new Converter())->convert($data);
        ExtraAsserts::assertIsArray($result);
        /** @var array $result */
        $this->assertCount(1, $result);
        $result = $result[ $key ];
        $this->assertCount(\count($data), $result);

        $this->assertArrayHasKey(TestModel::FIELD_NAME, $result[0]);
        $this->assertEquals($model_1->getVal(), $result[0][ TestModel::FIELD_NAME ]);
        $this->assertArrayHasKey(TestModel::FIELD_NAME, $result[1]);
        $this->assertEquals($model_2->getVal(), $result[1][ TestModel::FIELD_NAME ]);
    }

    /**
     * Checks if converter config can be completely overriden by the user config.
     */
    public function testConvertWithOverridenDefaultConfig(): void
    {
        // GIVEN built-in converter config
        /** @var array $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES) ?? [];
        ExtraAsserts::assertIsArray($cfg);
        $this->assertNotEmpty($cfg);

        // HAVING custom converter set to replace built-in settings
        $fake = new FakeConverter();

        $key = Generator::getRandomString();
        $cfg[ Collection::class ][ RB::KEY_HANDLER ] = \get_class($fake);
        $cfg[ Collection::class ][ RB::KEY_KEY ] = $key;
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, $cfg);

        // WHEN converting the data, we expect FakeConverter to be used
        $data = collect([1,
                         2,
                         3,
        ]);

        $result = (new Converter())->convert($data);

        ExtraAsserts::assertIsArray($result);
        /** @var array $result */
        $this->assertArrayHasKey($key, $result);
        $result = $result[ $key ];
        $this->assertCount(1, $result);
        $this->assertArrayHasKey($fake->key, $result);
        $this->assertEquals($result[ $fake->key ], $fake->val);
    }

    // ---------------------------------------------------------------------------------------------

    /**
     * Helper method that validates converted element with values from
     * source object.
     *
     * @param TestModel $obj  Source object converted.
     * @param array     $item Result of the conversion.
     */
    protected function assertValidConvertedTestClass(TestModel $obj, array $item): void
    {
        $this->assertArrayHasKey(TestModel::FIELD_NAME, $item);
        $this->assertEquals($obj->getVal(), $item[ TestModel::FIELD_NAME ]);
    }

} // end of class
