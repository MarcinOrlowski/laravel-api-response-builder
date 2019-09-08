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
		$min_code = Config::get(ResponseBuilder::CONF_KEY_MIN_CODE, null);

		if ($min_code === null) {
			throw new \RuntimeException(sprintf('CONFIG: Missing "%s" key', ResponseBuilder::CONF_KEY_MIN_CODE));
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
		$max_code = Config::get(ResponseBuilder::CONF_KEY_MAX_CODE, null);

		if ($max_code === null) {
			throw new \RuntimeException(sprintf('CONFIG: Missing "%s" key', ResponseBuilder::CONF_KEY_MAX_CODE));
		}

		return $max_code;
	}


	/**
	 * Returns lowest possible reserved code used by predefined Response Builder's messages
	 *
	 * @return integer
	 */
	protected static function getReservedMinCode(): int
	{
		return BaseApiCodes::RESERVED_MIN_API_CODE;
	}

	/**
	 * Returns highest possible reserved code used by predefined Response Builder's messages
	 *
	 * @return integer
	 */
	protected static function getReservedMaxCode()
	{
		return BaseApiCodes::RESERVED_MAX_API_CODE;
	}

	/**
	 * Returns array of error code constants defined in this class. Used mainly for debugging/tests
	 *
	 * @return array
	 */
	public static function getApiCodeConstants(): array
	{
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
	 * Returns locale mappings for given base api code or @null if there's no mapping
	 *
	 * @param integer $code Base (built-in) code to look for mapped message for.
	 *
	 * @return string|null
	 *
	 * @throws \InvalidArgumentException If $code is not in allowed reserved range.
	 */
	public static function getReservedCodeMessageKey($code): ?string
	{
		if (($code < BaseApiCodes::RESERVED_MIN_API_CODE) || ($code > BaseApiCodes::RESERVED_MAX_API_CODE)) {
			throw new \InvalidArgumentException(
				sprintf('Base code value (%d) is out of allowed reserved range %d-%d',
					$code, BaseApiCodes::RESERVED_MIN_API_CODE, BaseApiCodes::RESERVED_MAX_API_CODE));
		}

		$base_map = BaseApiCodes::getBaseMap();
		return $base_map[ $code ] ?? null;
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
	public static function getCodeMessageKey($api_code): ?string
	{
		if (!static::isCodeValid($api_code)) {
			$msg = sprintf('API code value (%d) is out of allowed range %d-%d',
				$api_code, static::getMinCode(), static::getMaxCode());
			throw new \InvalidArgumentException($msg);
		}

		$map = static::getMap();

		return $map[ $api_code ] ?? null;
	}

	/**
	 * Checks if given $code can is valid in this module and can be safely used
	 *
	 * @param integer $code Code to check
	 *
	 * @return boolean
	 */
	public static function isCodeValid($code): bool
	{
		$result = false;

		if ((($code >= static::getMinCode()) && ($code <= static::getMaxCode()))
			|| (($code <= static::getReservedMaxCode()) && ($code >= static::getReservedMinCode()))
		) {
			$result = true;
		}

		return $result;
	}

}
