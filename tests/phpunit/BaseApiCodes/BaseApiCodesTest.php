<?php
/**
 * @noinspection PhpDocMissingThrowsInspection
 */
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\BaseApiCodes;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class BaseApiCodesTest
 */
class BaseApiCodesTest extends TestCase
{
    /**
     * Tests getMinCode() with invalid config
     */
    public function testGetMinCodeMissingConfigKey(): void
    {
        $this->expectException(Ex\MissingConfigurationKeyException::class);

        /** @noinspection PhpUndefinedClassInspection */
        \Config::offsetUnset(RB::CONF_KEY_MIN_CODE);
	    /** @noinspection PhpUnhandledExceptionInspection */
	    BaseApiCodes::getMinCode();
    }

    /**
     * Tests getMaxCode() with invalid config
     */
    public function testGetMaxCodeMissingConfigKey(): void
    {
        $this->expectException(Ex\MissingConfigurationKeyException::class);

        /** @noinspection PhpUndefinedClassInspection */
        \Config::offsetUnset(RB::CONF_KEY_MAX_CODE);
	    /** @noinspection PhpUnhandledExceptionInspection */
        BaseApiCodes::getMaxCode();
    }

    /**
     * Tests getMap() with missing config
     */
    public function testGetMapMissingConfigKey(): void
    {
        $this->expectException(Ex\MissingConfigurationKeyException::class);

        /** @noinspection PhpUndefinedClassInspection */
        \Config::offsetUnset(RB::CONF_KEY_MAP);
	    /** @noinspection PhpUnhandledExceptionInspection */
        BaseApiCodes::getMap();
    }

    /**
     * Tests getMap() with wrong config
     */
    public function testGetMapWrongConfig(): void
    {
        $this->expectException(Ex\InvalidTypeException::class);

        /** @noinspection PhpUndefinedClassInspection */
        \Config::set(RB::CONF_KEY_MAP, false);
	    /** @noinspection PhpUnhandledExceptionInspection */
        BaseApiCodes::getMap();
    }

} // end of class
