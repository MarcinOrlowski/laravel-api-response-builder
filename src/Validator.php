<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

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

use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

/**
 * Data validator helper
 */
final class Validator
{
	/**
	 * Checks if given $val is of type boolean
	 *
	 * @param string $var_name Name of the key to be used if exception is thrown.
	 * @param mixed  $value    Variable to be asserted.
	 *
	 * @return void
	 *
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\NotBooleanException
	 *
	 */
	public static function assertIsBool(string $var_name, $value): void
	{
		self::assertIsType($var_name, $value, [Type::BOOLEAN], Ex\NotBooleanException::class);
	}

	/**
	 * Checks if given $val is of type integer
	 *
	 * @param string $var_name Name of the key to be used if exception is thrown.
	 * @param mixed  $value    Variable to be asserted.
	 *
	 * @return void
	 *
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\NotIntegerException
	 */
	public static function assertIsInt(string $var_name, $value): void
	{
		self::assertIsType($var_name, $value, [Type::INTEGER], Ex\NotIntegerException::class);
	}

	/**
	 * Checks if given $val is of type array
	 *
	 * @param string $var_name Name of the key to be used if exception is thrown.
	 * @param mixed  $value    Variable to be asserted.
	 *
	 * @return void
	 *
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\NotArrayException
	 */
	public static function assertIsArray(string $var_name, $value): void
	{
		self::assertIsType($var_name, $value, [Type::ARRAY], Ex\NotArrayException::class);
	}

	/**
	 * Checks if given $val is an object
	 *
	 * @param string $var_name Name of the key to be used if exception is thrown.
	 * @param mixed  $value    Variable to be asserted.
	 *
	 * @return void
	 *
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\NotObjectException
	 */
	public static function assertIsObject(string $var_name, $value): void
	{
		self::assertIsType($var_name, $value, [Type::OBJECT], Ex\NotObjectException::class);
	}

	/**
	 * Checks if given $cls_cls_or_obj is either an object or name of existing class.
	 *
	 * @param string        $var_name
	 * @param string|object $cls_or_obj
	 *
	 * @return void
	 *
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\ClassNotFound
	 */
	public static function assertIsObjectOrExistingClass(string $var_name, $cls_or_obj): void
	{
		self::assertIsType($var_name, $cls_or_obj, [Type::EXISTING_CLASS, Type::OBJECT]);
	}

	/**
	 * Checks if given $val is of type string
	 *
	 * @param string $var_name Label or name of the variable to be used in exception message (if thrown).
	 * @param mixed  $value    Variable to be asserted.
	 *
	 * @return void
	 *
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\NotStringException
	 */
	public static function assertIsString(string $var_name, $value): void
	{
		self::assertIsType($var_name, $value, [Type::STRING], Ex\NotStringException::class);
	}

	/**
	 * @param string $var_name Label or name of the variable to be used in exception message (if thrown).
	 * @param mixed  $value    Variable to be asserted.
	 * @param int    $min      Min allowed value (inclusive)
	 * @param int    $max      Max allowed value (inclusive)
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 * @throws \OutOfBoundsException
	 * @throws Ex\NotIntegerException
	 * @throws Ex\InvalidTypeException
	 */
	public static function assertIsIntRange(string $var_name, $value, int $min, int $max): void
	{
		self::assertIsInt($var_name, $value);

		if ($min > $max) {
			throw new \InvalidArgumentException(
				\sprintf('%s: Invalid range for "%s". Ensure bound values are not swapped.', __FUNCTION__, $var_name));
		}

		if (($min > $value) || ($value > $max)) {
			throw new \OutOfBoundsException(
				\sprintf('Value of "%s" (%d) is out of bounds. Must be between %d-%d inclusive.', $var_name, $value, $min, $max));
		}
	}

	/**
	 * Checks if $item (of name $key) is of type that is include in $allowed_types (there's `OR` connection
	 * between specified types).
	 *
	 * @param string $var_name      Label or name of the variable to be used in exception message (if thrown).
	 * @param mixed  $value         Variable to be asserted.
	 * @param array  $allowed_types Array of allowed types for $value, i.e. [Type::INTEGER]
	 * @param string $ex_class      Name of exception class (which implements InvalidTypeExceptionContract) to
	 *                              be used when assertion fails. In that case object of that class will be
	 *                              instantiated and thrown.
	 *
	 * @return void
	 *
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\ClassNotFound
	 */
	public static function assertIsType(string $var_name, $value, array $allowed_types,
	                                    string $ex_class = Ex\InvalidTypeException::class): void
	{
		// Type::EXISTING_CLASS is artificial type, so we need separate logic to handle it.
		$tmp = $allowed_types;
		$idx = array_search(Type::EXISTING_CLASS, $tmp, true);
		if ($idx !== false) {
			// Remove the type, so gettype() test loop won't see it.
			unset($tmp[$idx]);
			if (is_string($value) && class_exists($value)) {
				// It's existing class, no need to test further.
				return;
			}
		}

		$type = \gettype($value);
		if (!empty($tmp)) {
			if (!\in_array($type, $allowed_types, true)) {
				// FIXME we need to ensure $ex_class implements InvalidTypeExceptionContract at some point.
				throw new $ex_class($var_name, $type, $allowed_types);
			}
		} else {
			// FIXME we need to ensure $ex_class implements InvalidTypeExceptionContract at some point.
			throw new Ex\ClassNotFound($var_name, $type, $allowed_types);
		}
	}

	/**
	 * Ensures given $http_code is valid code for error response.
	 *
	 * @param int $http_code
	 *
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\NotIntegerException
	 */
	public static function assertErrorHttpCode(int $http_code): void
	{
		self::assertIsInt('http_code', $http_code);
		self::assertIsIntRange('http_code', $http_code,
			RB::ERROR_HTTP_CODE_MIN, RB::ERROR_HTTP_CODE_MAX);
	}

	/**
	 * Ensures given $http_code is valid for response indicating sucessful operation.
	 *
	 * @param int $http_code
	 *
	 * @throws Ex\InvalidTypeException
	 * @throws Ex\NotIntegerException
	 */
	public static function assertOkHttpCode(int $http_code): void
	{
		self::assertIsInt('http_code', $http_code);
		self::assertIsIntRange('http_code', $http_code, 200, 299);
	}

	/**
	 * Ensures $obj (that is value coming from variable, which name is passed in $label) is instance of $cls class.
	 *
	 * @param string $var_name Name of variable that the $obj value is coming from. Used for exception message.
	 * @param object $obj      Object to check instance of
	 * @param string $cls      Target class we want to check $obj agains.
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function assertInstanceOf(string $var_name, object $obj, string $cls): void
	{
		if (!($obj instanceof $cls)) {
			throw new \InvalidArgumentException(
				\sprintf('"%s" must be instance of "%s".', $var_name, $cls)
			);
		}
	}

	/**
	 * Ensure that we either have array with user provided keys i.e. ['foo'=>'bar'], which will then
	 * be turned into JSON object or array without user specified keys (['bar']) which we would return as JSON
	 * array. But you can't mix these two as the final JSON would not produce predictable results.
	 *
	 * @param array $data
	 *
	 * @throws Ex\ArrayWithMixedKeysException
	 */
	public static function assertArrayHasNoMixedKeys(array $data): void
	{
		$string_keys_cnt = 0;
		$int_keys_cnt = 0;
		foreach (\array_keys($data) as $key) {
			if (\is_int($key)) {
				if ($string_keys_cnt > 0) {
					throw new Ex\ArrayWithMixedKeysException();
				}
				$int_keys_cnt++;
			} else {
				if ($int_keys_cnt > 0) {
					throw new Ex\ArrayWithMixedKeysException();
				}
				$string_keys_cnt++;
			}
		}
	}
}
