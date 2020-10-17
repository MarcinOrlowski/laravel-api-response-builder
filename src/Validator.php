<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

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
class Validator
{
    /**
     * Checks if given $val is of type boolean
     *
     * @param string $var_name Name of the key to be used if exception is thrown.
     * @param mixed  $value    Variable to be asserted.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsBool(string $var_name, $value): void
    {
        self::assertIsType($var_name, $value, [Type::BOOLEAN]);
    }

    /**
     * Checks if given $val is of type integer
     *
     * @param string $key   Name of the key to be used if exception is thrown.
     * @param mixed  $value Variable to be asserted.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsInt(string $key, $value): void
    {
        self::assertIsType($key, $value, [Type::INTEGER]);
    }

    /**
     * Checks if given $val is of type array
     *
     * @param string $var_name Name of the key to be used if exception is thrown.
     * @param mixed  $value    Variable to be asserted.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsArray(string $var_name, $value): void
    {
        self::assertIsType($var_name, $value, [Type::ARRAY]);
    }

    /**
     * Checks if given $val is an object
     *
     * @param string $var_name Name of the key to be used if exception is thrown.
     * @param mixed  $value    Variable to be asserted.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsObject(string $var_name, $value): void
    {
        self::assertIsType($var_name, $value, [Type::OBJECT]);
    }

    /**
     * Checks if given $val is of type string
     *
     * @param string $name  Label or name of the variable to be used in exception message (if thrown).
     * @param mixed  $value Variable to be asserted.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsString(string $name, $value): void
    {
        self::assertIsType($name, $value, [Type::STRING]);
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
     * @throws \RuntimeException
     */
    public static function assertIsIntRange(string $var_name, $value, int $min, int $max): void
    {
        self::assertIsInt($var_name, $value);

        if ($min > $max) {
            throw new \RuntimeException(
                \sprintf('%s: Invalid range for "%s". Ensure bound values are not swapped.', __FUNCTION__, $var_name));
        }

        if (($min > $value) || ($value > $max)) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid value of "%s" (%d). Must be between %d-%d inclusive.', $var_name, $value, $min, $max));
        }
    }

    /**
     * Checks if $item (of name $key) is of type that is include in $allowed_types.
     *
     * @param string $var_name      Label or name of the variable to be used in exception message (if thrown).
     * @param mixed  $value         Variable to be asserted.
     * @param array  $allowed_types Array of allowed types for $var, i.e. [Type::INTEGER]
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function assertIsType(string $var_name, $value, array $allowed_types): void
    {
        $type = \gettype($value);
        if (!\in_array($type, $allowed_types, true)) {
            throw new \InvalidArgumentException(
                \sprintf('"%s" must be one of allowed types: %s (%s given).',
                    $var_name, implode(', ', $allowed_types), \gettype($value))
            );
        }
    }

    /**
     * Ensures given $http_code is valid code for error response.
     *
     * @param int $http_code
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
     */
    public static function assertInstanceOf(string $var_name, object $obj, string $cls): void
    {
        if (!($obj instanceof $cls)) {
            throw new \InvalidArgumentException(
                \sprintf('"%s" must be instance of "%s".', $var_name, $cls)
            );
        }
    }
}
