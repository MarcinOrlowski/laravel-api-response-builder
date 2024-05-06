<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PhpDocMissingThrowsInspection
 */
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Config;
use MarcinOrlowski\Lockpick\Lockpick;
use MarcinOrlowski\PhpunitExtraAsserts\Generator;
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModel;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Class AutoConversionTest
 */
class AutoConversionTest extends TestCase
{
    /**
     * Tests if buildResponse() would properly handle auto conversion
     */
    public function testClassAutoConversionSingleElement(): void
    {
        // GIVEN model object with randomly set member value
        $model_val = Generator::getRandomString('model');
        $model = new TestModel($model_val);

        // AND having its class configured for auto conversion
        $model_class_name = \get_class($model);
        $key = Generator::getRandomString();
        $cfg = [
            $model_class_name => [
                RB::KEY_HANDLER => ToArrayConverter::class,
                RB::KEY_KEY     => $key,
            ],
        ];
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, $cfg);

        // WHEN this object is returned
        $this->response = RB::success($model);
        $api = $this->getResponseSuccessObject();

        // THEN returned response object should have it auto converted
        $data = $api->getData();
        $this->assertNotNull($data);
        $this->assertIsArray($data);
        /** @var array $data */
        $this->assertArrayHasKey($key, $data);
        $this->assertEquals($model_val, $data[ $key ]['val']);
    }

    /**
     * Tests if buildResponse() would properly handle auto conversion when mapped
     * class is part of bigger data set.
     */
    public function testClassAutoConversionAsPartOfDataset(): void
    {
        // GIVEN model object with randomly set member value
        $model_1_val = Generator::getRandomString('model_1');
        $model_1 = new TestModel($model_1_val);

        $model_2_val = Generator::getRandomString('model_2');
        $model_2 = new TestModel($model_2_val);

        $model_1_data_key = 'model-data-key_1';
        $model_2_data_key = 'model-data-key_2';

        // AND having its class configured for auto conversion
        $model_class_name = \get_class($model_1);
        $converter = [
            $model_class_name => [
                RB::KEY_KEY     => 'should-not-be-used',
                RB::KEY_HANDLER => ToArrayConverter::class,
            ],
        ];
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, $converter);

        // AND having the object as part of bigger data set
        $tmp_base = [];
        for ($i = 0; $i < 1; $i++) {
            $tmp_base[ Generator::getRandomString("key{$i}") ] = Generator::getRandomString("val{$i}");
        }

        $data = $tmp_base;
        $data[ $model_1_data_key ] = $model_1;
        $data['nested'] = [];
        $data['nested'][ $model_2_data_key ] = $model_2;

        // WHEN this object is returned
        $this->response = RB::success($data);
        $api = $this->getResponseSuccessObject();

        // THEN returned response object should have it auto converted properly
        $data = $api->getData();
        $this->assertIsArray($data);
        $this->assertNotNull($data);
        /** @var array $data */

        // single key item must not be used
        /** @noinspection OffsetOperationsInspection */
        $this->assertArrayNotHasKey($converter[ $model_class_name ]['key'], $data,
            'Single item key found but should not');
        // instead original key must be preserved
        $this->assertArrayHasKey($model_1_data_key, $data,
            "Unable to find '{$model_1_data_key}' model 1 key'");
        $this->assertEquals($model_1_val, $data[ $model_1_data_key ]['val']);

        $this->assertArrayHasKey('nested', $data);
        $this->assertArrayHasKey($model_2_data_key, $data['nested'],
            "Unable to find '{$model_2_data_key}' model 2 key'");
        $this->assertEquals($model_2_val, $data['nested'][ $model_2_data_key ]['val']);

        // and all other elements of data set should also be here
        foreach ($tmp_base as $key => $val) {
            $this->assertArrayHasKey($key, $data);
            $this->assertEquals($val, $data[ $key ]);
        }
    }

    /**
     * Checks if buildResponse() would accept support payload types
     */
    #[DataProvider('successWithPrimitiveProvider')]
    public function testSuccessWithPrimitive(mixed $value): void
    {
        $this->response = RB::success($value);
        $api = $this->getResponseSuccessObject();

        // THEN returned response object should have it auto converted
        $data = $api->getData();
        $this->assertNotNull($data);
        $this->assertIsArray($data);
        /** @var array $data */

        $converter = new Converter();
        $cfg = Lockpick::call($converter, 'getPrimitiveMappingConfigOrThrow', [\gettype($value)]);
        $this->assertIsArray($cfg);
        $this->assertNotEmpty($cfg);
        /** @var array $cfg */
        $key = $cfg[ RB::KEY_KEY ];
        $this->assertArrayHasKey($key, $data);
        $this->assertEquals($value, $data[ $key ]);
    }

    public static function successWithPrimitiveProvider(): array
    {
        return [
            // boolean
            [(bool)\random_int(0, 1)],
            // integer
            [random_int(0, 10000)],
            // double
            [((double)random_int(0, 10000)) / random_int(1, 100)],
            // string
            [Generator::getRandomString()],
        ];
    }

} // end of class
