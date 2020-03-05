<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

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

use Illuminate\Support\Facades\Config;

/**
 * Reusable ApiCodeBase related methods
 */
trait ApiCodesHelpers
{
    /**
     * Returns lowest allowed error code for this module
     *
     * @return integer
     *
     * @throws \RuntimeException Throws exception if no min_code set up
     */
    public static function getMinCode(): int
    {
        $key = ResponseBuilder::CONF_KEY_MIN_CODE;
        $min_code = Config::get($key, null);

        if ($min_code === null) {
            throw new \RuntimeException(sprintf('CONFIG: Missing "%s" key', $key));
        }

        return $min_code;
    }

    /**
     * Returns highest allowed error code for this module
     *
     * @return integer
     *
     * @throws \RuntimeException Throws exception if no max_code set up
     */
    public static function getMaxCode(): int
    {
        $key = ResponseBuilder::CONF_KEY_MAX_CODE;
        $max_code = Config::get($key, null);

        if ($max_code === null) {
            throw new \RuntimeException(sprintf('CONFIG: Missing "%s" key', $key));
        }

        return $max_code;
    }

    /**
     * Returns array of error code constants defined in this class. Used mainly for debugging/tests
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function getApiCodeConstants(): array
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return (new \ReflectionClass(static::class))->getConstants();
    }

    /**
     * Returns complete error code to locale string mapping array
     *
     * @return array
     *
     * @throws \RuntimeException Thrown when builder map is not configured.
     */
    public static function getMap(): array
    {
        $user_map = Config::get(ResponseBuilder::CONF_KEY_MAP, null);
        if ($user_map === null) {
            throw new \RuntimeException(sprintf('CONFIG: Missing "%s" key', ResponseBuilder::CONF_KEY_MAP));
        }
        if (!is_array($user_map)) {
            throw new \RuntimeException(sprintf('CONFIG: "%s" must be an array', ResponseBuilder::CONF_KEY_MAP));
        }
        return Util::mergeConfig(BaseApiCodes::getBaseMap(), $user_map);
    }

    /**
     * Returns locale mappings key for given api code or @null if there's no mapping
     *
     * @param integer $api_code Api code to look for mapped message for.
     *
     * @return string|null
     *
     * @throws \InvalidArgumentException If $code is not in allowed range.
     */
    public static function getCodeMessageKey(int $api_code): ?string
    {
        if (!static::isCodeValid($api_code)) {
            $min = static::getMinCode();
            $max = static::getMaxCode();
            throw new \InvalidArgumentException("API code value ({$api_code}) is out of allowed range {$min}-{$max}");
        }

        $map = static::getMap();

        return $map[ $api_code ] ?? null;
    }

    /**
     * Checks if given API $code can be used in current configuration.
     *
     * @param int $code API code to validate
     *
     * @return bool
     */
    public static function isCodeValid(int $code): bool
    {
        return ($code === 0) || (($code >= static::getMinCode()) && ($code <= static::getMaxCode()));
    }

    /**
     * Returns final API code for internal code, remapped to configured code range
     *
     * @param int $internal_code
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public static function getCodeForInternalOffset(int $internal_code): int
    {
        $min = static::RESERVED_MIN_API_CODE_OFFSET;
        $max = static::RESERVED_MAX_API_CODE_OFFSET;
        Validator::assertIsIntRange('internal_code', $internal_code, $min, $max);

        return ($internal_code === 0) ? 0 : $internal_code + static::getMinCode();
    }

}
