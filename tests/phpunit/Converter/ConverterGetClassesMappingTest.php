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
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class ConverterGetClassesMappingTest
 */
class ConverterGetClassesMappingTest extends TestCase
{
    /**
     * Checks if getClassesMapping would throw exception on invalid configuration data
     */
    public function testInvalidConfigurationData(): void
    {
        Config::set(RB::CONF_KEY_CONVERTER_CLASSES, 'invalid');

        $this->expectException(Ex\InvalidConfigurationException::class);

        Lockpick::call(Converter::class, 'getClassesMapping');
    }

    /**
     * Checks if getClassesMapping would return empty array if there's no "classes" config entry
     */
    public function testNoMappingConfig(): void
    {
        // Remove any classes config
        /** @noinspection PhpUndefinedMethodInspection */
        Config::offsetUnset(RB::CONF_KEY_CONVERTER_CLASSES);

        $result = Lockpick::call(Converter::class, 'getClassesMapping');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

} // end of class
