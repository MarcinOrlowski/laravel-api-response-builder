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
use MarcinOrlowski\ResponseBuilder\Tests\Converters\FakeConverter;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModel;
use MarcinOrlowski\ResponseBuilder\Type;

class ObjectTest extends TestCase
{
	/**
	 * Checks if convert returns data without any wrapper 'key' when it's set to be empty
	 */
	public function testConvertWithEmptyKeyInConfig(): void
	{
        // custom converter with it's own response structure
        $fake = new FakeConverter();

        $data = collect([1, 2, 3]);

        $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES);
        $cfg[ Collection::class ][ RB::KEY_HANDLER ] = FakeConverter::class;
        $cfg[ Collection::class ][ RB::KEY_KEY ] = null;

        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, $cfg);

        $result = (new Converter())->convert($data);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey($fake->key, $result);
        $this->assertEquals($result[ $fake->key ], $fake->val);
	}

    /**
     * Checks if exception is thrown for invalid 'key' type
     */
    public function testConvertWithInvalidKeyType(): void
    {
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, [
            Collection::class => [
                RB::KEY_KEY     => false,
                RB::KEY_HANDLER => FakeConverter::class,
            ],
        ]);

        $this->expectException(Ex\InvalidTypeException::class);

        ///** @noinspection PhpUnhandledExceptionInspection */
        $this->callProtectedMethod(Converter::class, 'getClassesMapping');
    }

    /**
     * Checks if convert works normal for valid 'key' types
     */
    public function testConvertWithValidKeyType(): void
    {
        // only string and null is allowed for RB::KEY_KEY
        $allowedKeys = ['xxx_string', NULL];

        $fake = new FakeConverter();

        $data = collect([1, 2, 3]);

        $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES);
        $cfg[ Collection::class ][ RB::KEY_HANDLER ] = FakeConverter::class;

        collect($allowedKeys)->each(function ($allowedKey) use($data, $fake, $cfg) {
            $cfg[ Collection::class ][ RB::KEY_KEY ] = $allowedKey;

            Config::set(RB::CONF_KEY_CONVERTER_CLASSES, $cfg);

            $result = (new Converter())->convert($data);

            $this->assertIsArray($result);
            $this->assertCount(1, $result);

            if(gettype($allowedKey) === TYPE::STRING) {
                $this->assertArrayHasKey($allowedKey, $result);
                $this->assertArrayHasKey($fake->key, $result[ $allowedKey ]);
                $this->assertEquals($result[ $allowedKey ][ $fake->key ], $fake->val);
            } else if(gettype($allowedKey) === TYPE::NULL) {
                $this->assertArrayHasKey($fake->key, $result);
                $this->assertEquals($result[ $fake->key ], $fake->val);
            }
        });
    }
}
