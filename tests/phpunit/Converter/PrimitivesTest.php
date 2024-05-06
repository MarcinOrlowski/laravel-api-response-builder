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

use Illuminate\Support\Facades\Config;
use MarcinOrlowski\Lockpick\Lockpick;
use MarcinOrlowski\PhpunitExtraAsserts\Generator;
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModel;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class PrimitivesTest
 */
class PrimitivesTest extends TestCase
{

    /**
     * Checks how we convert directly passed object
     */
    public function testDirectObject(): void
    {
        $model_val = Generator::getRandomString();
        $model = new TestModel($model_val);

        // AND having its class configured for auto conversion
        $key = Generator::getRandomString('key');
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, [
            \get_class($model) => [
                RB::KEY_HANDLER => ToArrayConverter::class,
                RB::KEY_KEY     => $key,
            ],
        ]);

        // WHEN this object is returned
        /** @var array $converted */
        $converted = (new Converter())->convert($model);

        // THEN we expect returned data to be converted and use KEY_KEY element.
        $this->assertIsArray($converted);
        $this->assertArrayHasKey($key, $converted);
        $this->assertCount(1, $converted[ $key ]);
        $this->assertEquals($model_val, $converted[ $key ][ TestModel::FIELD_NAME ]);
    }

    /**
     * Checks if passing boolean as direct payload works as expected.
     */
    public function testDirectBool(): void
    {
        // GIVEN primitive value
        $value = \random_int(0, 1);
        $this->doDirectPrimitiveTest($value);
    }

    /**
     * Checks if passing double as direct payload works as expected.
     */
    public function testDirectDouble(): void
    {
        // GIVEN primitive value
        $value = ((double)\random_int(0, 100000) / \random_int(1, 1000)) + 0.1;
        $this->doDirectPrimitiveTest($value);
    }

    /**
     * Checks if passing integer as direct payload works as expected.
     */
    public function testDirectInteger(): void
    {
        // GIVEN primitive value
        $value = \random_int(0, 10000);
        $this->doDirectPrimitiveTest($value);
    }

    /**
     * Checks if passing string as direct payload works as expected.
     */
    public function testDirectString(): void
    {
        // GIVEN primitive value
        $value = Generator::getRandomString();
        $this->doDirectPrimitiveTest($value);
    }

    /**
     * Helper method to perform some common tests for primitive as direct payload.
     */
    protected function doDirectPrimitiveTest(mixed $value): void
    {
        // GIVEN primitive value $value

        // WHEN passing it as direct payaload
        $converter = new Converter();
        /** @var array $converted */
        $converted = $converter->convert($value);

        // THEN we expect returned data to be keyed as per primitive's configuration.
        $this->assertIsArray($converted);

        /** @var array $cfg */
        $cfg = Lockpick::call($converter, 'getPrimitiveMappingConfigOrThrow', [$value]);
        $this->assertIsArray($cfg);
        $this->assertNotEmpty($cfg);
        /** @var string $key */
        $key = $cfg[ RB::KEY_KEY ];
        $this->assertArrayHasKey($key, $converted);
        $this->assertEquals($value, $converted[ $key ]);
    }

} // end of class
