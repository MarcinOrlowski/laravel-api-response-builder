<?php /** @noinspection PhpUndefinedClassInspection */

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

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class BaseApiCodesTest extends TestCase
{
    /**
     * Tests getMinCode() with invalid config
     *
     * @return void
     */
    public function testGetMinCodeMissingConfigKey(): void
    {
        $this->expectException(\RuntimeException::class);

        /** @noinspection PhpUndefinedClassInspection */
        \Config::offsetUnset(RB::CONF_KEY_MIN_CODE);
        BaseApiCodes::getMinCode();
    }

    /**
     * Tests getMaxCode() with invalid config
     *
     * @return void
     */
    public function testGetMaxCodeMissingConfigKey(): void
    {
        $this->expectException(\RuntimeException::class);

        /** @noinspection PhpUndefinedClassInspection */
        \Config::offsetUnset(RB::CONF_KEY_MAX_CODE);
        BaseApiCodes::getMaxCode();
    }

    /**
     * Tests getMap() with missing config
     *
     * @return void
     */
    public function testGetMapMissingConfigKey(): void
    {
        $this->expectException(\RuntimeException::class);

        /** @noinspection PhpUndefinedClassInspection */
        \Config::offsetUnset(RB::CONF_KEY_MAP);
        BaseApiCodes::getMap();
    }

    /**
     * Tests getMap() with wrong config
     *
     * @return void
     */
    public function testGetMapWrongConfig(): void
    {
        $this->expectException(\RuntimeException::class);

        /** @noinspection PhpUndefinedClassInspection */
        \Config::set(RB::CONF_KEY_MAP, false);
        BaseApiCodes::getMap();
    }
}
