<?php

namespace MarcinOrlowski\ResponseBuilder\Tests\Base;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

/**
 * Class TestCaseBase
 */
abstract class TestCaseBase extends \Orchestra\Testbench\TestCase
{
	/**
	 * Returns instance of your API codes class. Sufficient implementation of this method
	 * for most of the cases is just:
	 *
	 *   return new \App\ApiCodes();
	 *
	 * where \App\ApiCodes matches your codes class
	 *
	 * @return \MarcinOrlowski\ResponseBuilder\ApiCodeBase
	 */
	abstract public function getApiCodesObject();

	/**
	 * return object of your API codes class usually just:
	 *
	 *   return '\App\ApiCodes';
	 *
	 * or (PHP5.5+ only!)
	 *
	 *   return \App\ApiCodes::class;
	 *
	 * NOTE: MUST start with the "\"!
	 *
	 * @return string
	 */
	abstract public function getApiCodesClassName();

	/**
	 * Returns ErrorCode constant name referenced by its value
	 *
	 * @param int $api_code value to match constant name for
	 *
	 * @return int|null|string
	 */
	protected function resolveConstantFromCode($api_code)
	{
		/** @var \MarcinOrlowski\ResponseBuilder\ApiCodeBase $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();
		/** @var array $const */
		$const = $api_codes_class_name::getApiCodeConstants();
		$name = null;
		foreach ($const as $const_name => $const_value) {
			if (is_int($const_value) && ($const_value === $api_code)) {
				$name = $const_name;
				break;
			}
		}

		return ($name === null) ? "??? ({$api_code})" : $name;
	}

	/**
	 * Helper to let test protected/private methods
	 *
	 * Usage example:
	 * ----------------
	 *   $method = $this->getProtectedMethod('App\Foo', 'someMethod');
	 *   $obj = new \App\Foo();
	 *   $result = $method->invokeArgs($obj, ...);
	 *
	 * @param string $class_name  method's class name to, i.e. "Bar". Can be namespaced i.e. "Foo\Bar" (no starting backslash)
	 * @param string $method_name method name to call
	 *
	 * @return \ReflectionMethod
	 */
	protected function getProtectedMethod($class_name, $method_name)
	{
		$class = new \ReflectionClass($class_name);
		$method = $class->getMethod($method_name);
		$method->setAccessible(true);

		return $method;
	}

	/**
	 * Returns value of otherwise non-public member of the class
	 *
	 * @param string $class_name  class name to get member from
	 * @param string $member_name member name
	 *
	 * @return mixed
	 */
	protected function getProtectedMember($class_name, $member_name)
	{
		$reflection = new \ReflectionClass($class_name);
		$property = $reflection->getProperty($member_name);
		$property->setAccessible(true);

		return $property->getValue($class_name);
	}

	/**
	 * Generates random string, with optional prefix
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	protected function getRandomString($prefix = null)
	{
		if ($prefix !== null) {
			$prefix = "{$prefix}_";
		}

		return $prefix . md5(uniqid(mt_rand(), true));
	}


	/**
	 * UTF8 aware version of ord()
	 *
	 * @param string $string UTF8 string to work on
	 * @param int    $offset start offset. Note, offset will be updated to properly skip multibyte chars!
	 *
	 * $text = "abcàêß€abc";
	 * $offset = 0;
	 * while ($offset >= 0) {
	 *    printf("%d: %d\n", $offset, ord8($text, $offset));
	 * }
	 *
	 * @return int code of the character
	 */
	protected function ord8($string, &$offset)
	{
		$code = ord(substr($string, $offset, 1));
		if ($code >= 128) {             //otherwise 0xxxxxxx
			if ($code < 224) {          //110xxxxx
				$bytesnumber = 2;
			} elseif ($code < 240) {    //1110xxxx
				$bytesnumber = 3;
			} elseif ($code < 248) {    //11110xxx
				$bytesnumber = 4;
			}

			$codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
			for ($i = 2; $i <= $bytesnumber; $i++) {
				$offset++;
				$code2 = ord(substr($string, $offset, 1)) - 128;        //10xxxxxx
				$codetemp = $codetemp * 64 + $code2;
			}
			$code = $codetemp;
		}
		$offset += 1;
		if ($offset >= strlen($string)) {
			$offset = -1;
		}

		return $code;
	}

}
