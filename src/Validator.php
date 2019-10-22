<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */
class Validator
{
	/**
	 * Checks if given $val is of type integer
	 *
	 * @param string $key Name of the key to be used if exception is thrown.
	 * @param mixed  $val Value to validate.
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function assertInt(string $key, $val): void
	{
		if (!is_int($val)) {
			$msg = sprintf('"%s" must be an integer (%s given)', $key, gettype($val));
			throw new \InvalidArgumentException($msg);
		}
	}

	/**
	 * @param string $key
	 * @param mixed  $var
	 * @param int    $min
	 * @param int    $max
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public static function assertIntRange(string $key, $var, int $min, int $max): void
	{
		self::assertInt($key, $var);

		if ($min > $max) {
			throw new \RuntimeException(
				sprintf('%s: Invalid range for "%s". Ensure bound values are not swapped.', __FUNCTION__, $key));
		}

		if (($min > $var) || ($var > $max)) {
			throw new \InvalidArgumentException(
				sprintf('Invalid value of "%s" (%d). Must be between %d-%d inclusive.', $key, $var, $min, $max));
		}
	}
}
