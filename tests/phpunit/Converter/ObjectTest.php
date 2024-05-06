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
use MarcinOrlowski\Lockpick\Lockpick;
use MarcinOrlowski\PhpunitExtraAsserts\Generator;
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\Converter\Converters\FakeConverter;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class ObjectTest
 */
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

        /** @var array $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES) ?? [];
        $cfg[ Collection::class ][ RB::KEY_HANDLER ] = FakeConverter::class;
        $cfg[ Collection::class ][ RB::KEY_KEY ] = null;

        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, $cfg);

        /** @var array $result */
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

        Lockpick::call(Converter::class, 'getClassesMapping');
    }

    /**
     * Checks if convert works normal for valid 'key' types
     */
    public function testConvertWithValidKeyType(): void
    {
        // only string and null is allowed for RB::KEY_KEY
        $allowed_keys = [Generator::getRandomString(), null];

        $fake = new FakeConverter();

        $data = collect([1, 2, 3]);

        /** @var array $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES) ?? [];
        $cfg[ Collection::class ][ RB::KEY_HANDLER ] = FakeConverter::class;

        \collect($allowed_keys)->each(function($allowed_key) use ($data, $fake, $cfg) {
            $cfg[ Collection::class ][ RB::KEY_KEY ] = $allowed_key;

            Config::set(RB::CONF_KEY_CONVERTER_CLASSES, $cfg);

            $result = (new Converter())->convert($data);

            $this->assertIsArray($result);
            /** @var array $result */
            $this->assertCount(1, $result);

            if (\is_string($allowed_key)) {
                $this->assertArrayHasKey($allowed_key, $result);
                $this->assertArrayHasKey($fake->key, $result[ $allowed_key ]);
                $this->assertEquals($result[ $allowed_key ][ $fake->key ], $fake->val);
            } else if (\is_null($allowed_key)) {
                $this->assertArrayHasKey($fake->key, $result);
                $this->assertEquals($result[ $fake->key ], $fake->val);
            }
        });
    }

} // end of class
