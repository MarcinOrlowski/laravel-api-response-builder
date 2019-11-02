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
	/** @var string */
	public const TYPE_STRING = 'string';

	/** @var string */
	public const TYPE_INTEGER = 'integer';

	/** @var string */
	public const TYPE_BOOL = 'boolean';

	/**
	 * Checks if given $val is of type integer
	 *
	 * @param string $key Name of the key to be used if exception is thrown.
	 * @param mixed  $var Data to validated.
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function assertInt(string $key, $var): void
	{
		self::assertType($key, $var, [self::TYPE_INTEGER]);
	}

	/**
	 * Checks if given $val is of type string
	 *
	 * @param string $key Name of the key to be used if exception is thrown.
	 * @param mixed  $var Data to validated.
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function assertString(string $key, $var): void
	{
		self::assertType($key, $var, [self::TYPE_STRING]);
	}

	/**
	 * @param string $key Name of the key to be used if exception is thrown.
	 * @param mixed  $var Data to validated.
	 * @param int    $min Min allowed value (inclusive)
	 * @param int    $max Max allowed value (inclusive)
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

	/**
	 * Checks if $item (of name $key) is of type that is include in $allowed_types.
	 *
	 * @param string $key
	 * @param mixed  $var
	 * @param array[string]  $allowed_types
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function assertType(string $key, $var, array $allowed_types): void
	{
		$type = gettype($var);
		if (!in_array($type, $allowed_types)) {
			$msg = sprintf('"%s" must be one of allowed types: %s (%s given)', $key, implode(', ', $allowed_types), gettype($var));
			throw new \InvalidArgumentException($msg);
		}
	}
}
