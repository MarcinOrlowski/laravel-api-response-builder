<?php

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

abstract class TestCaseBase extends Orchestra\Testbench\TestCase
{
	// return object of your API codes class usually just:
	//
	// return new \App\ApiCodes();
	abstract function getApiCodesObject();

	// return object of your API codes class usually just:
	//
	// return '\App\ApiCodes';
	abstract function getApiCodesClassName();


	/**
	 * Returns ErrorCode constant name referenced by its value
	 *
	 * @param $error_code
	 *
	 * @return int|null|string
	 */
	protected function resolveConstantFromCode($error_code) {
		$api_codes = $this->getApiCodesClassName();
		/** @var array $const */
		$const = $api_codes::getErrorCodeConstants();
		$name = null;
		foreach( $const as $const_name => $const_value ) {
			if( is_int($const_value) && ($const_value === $error_code) ) {
				$name = $const_name;
				break;
			}
		}

		return ($name === null) ? "??? ({$error_code})" : $name;
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
	 * @param string $class_name  name of the class method belongs to, i.e. "Bar". Can be namespaced i.e. "Foo\Bar" (no starting backslash)
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

}
