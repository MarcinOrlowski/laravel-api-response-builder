<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
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
	 * Returns max allowed code offset for this range.
	 *
	 * @return int
	 */
	public static function getMaxCodeOffset(): int
	{
		return static::getMaxCode() - static::getMinCode();
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
		$reflect = new \ReflectionClass(get_called_class());
		return $reflect->getConstants();
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
		$map = Config::get(ResponseBuilder::CONF_KEY_MAP, null);
		if ($map === null) {
			throw new \RuntimeException(sprintf('CONFIG: Missing "%s" key', $map));
		}

		if (!is_array($map)) {
			throw new \RuntimeException(sprintf('CONFIG: "%s" must be an array', $map));
		}

		/** @noinspection AdditionOperationOnArraysInspection */
		return $map + BaseApiCodes::getBaseMap();
	}

	/**
	 * Returns locale mappings key for given api code or @null if there's no mapping
	 *
	 * @param integer $api_code_offset Api code to look for mapped message for.
	 *
	 * @return string|null
	 *
	 * @throws \InvalidArgumentException If $code is not in allowed range.
	 */
	public static function getCodeMessageKey($api_code_offset): ?string
	{
		if (!static::isCodeOffsetValid($api_code_offset)) {
			$max_code_offset = static::getMaxCodeOffset();
			$msg = "API code offset value ({$api_code_offset}) is out of allowed range 0-{$max_code_offset}";
			throw new \InvalidArgumentException($msg);
		}

		$map = static::getMap();

		return $map[ $api_code_offset ] ?? null;
	}

	/**
	 * Checks if given $code_offset is valid in this module and can be safely used
	 *
	 * @param integer $code_offset Code offset to check
	 *
	 * @return boolean
	 */
	public static function isCodeOffsetValid($code_offset): bool
	{
		return ($code_offset >= 0) && ($code_offset <= static::getMaxCodeOffset());
	}

}
