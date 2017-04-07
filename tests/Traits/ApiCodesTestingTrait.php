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

namespace MarcinOrlowski\ResponseBuilder\Tests\Traits;

use MarcinOrlowski\ResponseBuilder\ApiCodeBase;

/**
 * App testing helper trait
 */
trait ApiCodesTestingTrait
{
	use ResponseBuilderTestHelper;


	/**
	 * Checks if Api codes range is set right
	 *
	 * @return void
	 */
	public function testMinMaxCode()
	{
		$obj = new ApiCodeBase();

		$get_base_max_code = $this->getProtectedMethod(get_class($obj), 'getReservedMaxCode');
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
	 * Checks if all Api codes defined in ApiCodes class contain mapping entry
	 *
	 * @return void
	 */
	public function testIfAllCodesGotMapping()
	{
		/** @var ApiCodeBase $api_codes */
		$api_codes = $this->getApiCodesClassName();
		/** @var array $codes */

		$reflect = new \ReflectionClass($api_codes);
		$codes = $reflect->getConstants();
		foreach ($codes as $name => $val) {
			$this->assertNotNull($api_codes::getCodeMessageKey($val), "No mapping for {$name}");
		}
	}

	/**
	 * Checks if all Api codes are in correct and allowed range,
	 *
	 * @return void
	 */
	public function testIfAllCodesAreInRange()
	{
		/** @var ApiCodeBase $api_codes */
		$api_codes = $this->getApiCodesClassName();
		/** @var array $codes */
		$codes = $api_codes::getApiCodeConstants();
		foreach ($codes as $name => $val) {
			$msg = sprintf("Value of '{$name}' ({$val}) is out of allowed range %d-%d",
				$api_codes::getMinCode(), $api_codes::getMaxCode());

			$this->assertTrue($api_codes::isCodeValid($val), $msg);
		}
	}

	/**
	 * Checks if all defined Api code constants' values are unique
	 *
	 * @return void
	 */
	public function testIfAllApiValuesAreUnique()
	{
		/** @var ApiCodeBase $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();
		$items = array_count_values($api_codes_class_name::getMap());
		foreach ($items as $code => $count) {
			$this->assertLessThanOrEqual($count, 1, sprintf("Value of  '{$code}' is not unique. Used {$count} times."));
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

}
