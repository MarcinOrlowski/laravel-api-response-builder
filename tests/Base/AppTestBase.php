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

namespace MarcinOrlowski\ResponseBuilder\Tests\Base;

use MarcinOrlowski\ResponseBuilder\ApiCodeBase;

/**
 * Class AppTestBase
 */
abstract class AppTestBase extends ResponseBuilderTestCaseBase
{
	/**
	 * Checks if error codes range is set right
	 *
	 * @return void
	 */
	public function testMinMaxCode()
	{

		$obj = $this->getApiCodesObject();

		$get_base_max_code = $this->getProtectedMethod(get_class($obj), 'getBaseMaxCode');
		$base_max = $get_base_max_code->invokeArgs($obj, []);

		$get_min_code = $this->getProtectedMethod(get_class($obj), 'getMinCode');
		$min = $get_min_code->invokeArgs($obj, []);

		$get_max_code = $this->getProtectedMethod(get_class($obj), 'getMaxCode');
		$max = $get_max_code->invokeArgs($obj, []);

		$this->assertNotNull($base_max);
		$this->assertNotNull($min);
		$this->assertNotNull($max);

		$this->assertTrue($min > $base_max);
		$this->assertTrue($max > $min);
	}

	/**
	 * Checks if all error codes defined in ApiCode class contain mapping entry
	 *
	 * @return void
	 */
	public function testIfAllCodesGotMapping()
	{
		/** @var ApiCodeBase $api_codes */
		$api_codes = $this->getApiCodesClassName();
		/** @var array $codes */
		$codes = $api_codes::getErrorCodeConstants();
		foreach ($codes as $name => $val) {
			$this->assertNotNull($api_codes::getMapping($val), "No mapping for {$name}");
		}
	}

	/**
	 * Checks if all error codes are in allowed range
	 *
	 * @return void
	 */
	public function testIfAllCodesAreInRange()
	{
		/** @var ApiCodeBase $api_codes */
		$api_codes = $this->getApiCodesClassName();
		/** @var array $codes */
		$codes = $api_codes::getErrorCodeConstants();
		foreach ($codes as $name => $val) {
			$this->assertTrue($api_codes::isCodeValid($val), "Value of {$name} is outside allowed range");
		}
	}

	/**
	 * Checks if all defined error code constants are unique (per value)
	 *
	 * @return void
	 */
	public function testIfAllErrorValuesAreUnique()
	{
		/** @var ApiCodeBase $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();
		$items = array_count_values($api_codes_class_name::getMap());
		foreach ($items as $code => $count) {
			$this->assertLessThanOrEqual($count, 1, sprintf("Error code {$code} is not unique. Used {$count} times."));
		}
	}

	/**
	 * Checks if all codes are mapped to existing locale strings
	 *
	 * TODO: check translations too
	 *
	 * @return void
	 */
	public function testIfAllCodesAreCorrectlyMapped()
	{
		/** @var ApiCodeBase $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();
		/** @var array $map */
		$map = $api_codes_class_name::getMap();
		foreach ($map as $code => $mapping) {
			$str = \Lang::get($mapping);
			$this->assertNotEquals($mapping, $str,
				sprintf('No lang entry for: %s referenced by %s', $mapping, $this->resolveConstantFromCode($code))
			);
		}
	}


	/**
	 * Checks make() for code with missing message mapping
	 *
	 * @return void
	 */
	public function testMake_MissingMapping()
	{
		$min = $this->min_allowed_code;
		$max = $this->max_allowed_code;

		/** @var ApiCodeBase $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();
		$map = $api_codes_class_name::getMap();
		krsort($map);
		reset($map);

		$message_or_api_code = null;
		for ($i = $min; $i < $max; $i++) {
			if (array_key_exists($i, $map) === false) {
				$message_or_api_code = $i;
				break;
			}
		}

		if ($message_or_api_code === null) {
			$this->fail('Failed to find unused error code value (within declared range) to perform this test');
		}

		$this->callMakeMethod($message_or_api_code, $message_or_api_code);

		$json_object = json_decode($this->response->getContent());
		$this->assertTrue(is_object($json_object));
		$this->assertEquals(\Lang::get($api_codes_class_name::getMapping(ApiCodeBase::NO_ERROR_MESSAGE),
			['error_code' => $message_or_api_code]), $json_object->message);
	}

	/**
	 * Tests if your ApiCodes class is instance of base ResponseBuilder class
	 *
	 * @return void
	 */
	public function testErrorCodesSubclassOfErrorCode()
	{
		$base_class = ApiCodeBase::class;
		$api_codes = $this->getApiCodesObject();

		$this->assertInstanceOf($api_codes, $base_class);
	}

}
