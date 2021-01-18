<?php

namespace MarcinOrlowski\ResponseBuilder\Tests\Traits;

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

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * ApiCodes tests trait. Use this trait to test your ApiCodes class.
 * NOTE: that this trait reads class constants, therefore using it with any other class, or ApiCode class not based
 * on recommended `const`s will not work.
 *
 * Please see [docs/testing.md](docs/testing.md) for more info about testing own code with provided helpers.
 */
trait ApiCodesTests
{
	use TestingHelpers;

	/**
	 * Returns array of constant names that should be ignored during other
	 * tests.
	 *
	 * @return array
	 */
	protected function getConstantsToIgnore(): array
	{
		return [
			'RESERVED_MIN_API_CODE_OFFSET',
			'RESERVED_MAX_API_CODE_OFFSET',
		];
	}

	/**
	 * Returns array of constant names that are now turned into
	 * regular methods, so these methods will be now called
	 * by other tests.
	 *
	 * @return array
	 */
	protected function getConstantsThatAreNowMethods(): array
	{
		return ['OK_OFFSET',
		        'NO_ERROR_MESSAGE_OFFSET',
		        'EX_HTTP_NOT_FOUND_OFFSET',
		        'EX_HTTP_SERVICE_UNAVAILABLE_OFFSET',
		        'EX_HTTP_EXCEPTION_OFFSET',
		        'EX_UNCAUGHT_EXCEPTION_OFFSET',
		        'EX_AUTHENTICATION_EXCEPTION_OFFSET',
		        'EX_VALIDATION_EXCEPTION_OFFSET',];
	}

	/**
	 * Checks if Api codes range is set right
	 *
	 * @return void
	 *
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	public function testMinMaxCode(): void
	{
		$min = $this->callProtectedMethod(BaseApiCodes::class, 'getMinCode');
		$this->assertNotNull($min);

		$max = $this->callProtectedMethod(BaseApiCodes::class, 'getMaxCode');
		$this->assertNotNull($max);

		$this->assertTrue($max > $min);
	}

	/**
	 * Checks if defined code range is large enough to accommodate built-in codes.
	 *
	 * @return void
	 */
	public function testCodeRangeIsLargeEnough(): void
	{
		$base_max = BaseApiCodes::RESERVED_MAX_API_CODE_OFFSET;
		$min = $this->callProtectedMethod(BaseApiCodes::class, 'getMinCode');
		$max = $this->callProtectedMethod(BaseApiCodes::class, 'getMaxCode');

		$this->assertTrue(($max - $min) > $base_max);
	}

	/**
	 * Checks if all Api codes defined in ApiCodes class contain mapping entry.
	 *
	 * @return void
	 *
	 * @throws \ReflectionException
	 */
	public function testIfAllCodesGotMapping(): void
	{
		$const_to_ignore = $this->getConstantsToIgnore();
		$consts_methods = $this->getConstantsThatAreNowMethods();

		/** @var BaseApiCodes $api_codes */
		$api_codes = $this->getApiCodesClassName();
		$codes = $api_codes::getApiCodeConstants();

		foreach ($codes as $name => $val) {
			if (\in_array($name, $const_to_ignore, true)) {
				$this->assertTrue(true);
				continue;
			}

			if (\in_array($name, $consts_methods, true)) {
				$name = \str_replace('_OFFSET', '', $name);
				$val = BaseApiCodes::$name();
			}

			$this->assertNotNull($api_codes::getCodeMessageKey($val), "No message mapping for {$name} found.");
		}
	}

	/**
	 * Checks if all Api codes are in correct and allowed range.
	 *
	 * @return void
	 */
	public function testIfAllCodesAreInRange(): void
	{
		$const_to_ignore = $this->getConstantsToIgnore();
		$const_now_method = $this->getConstantsThatAreNowMethods();

		/** @var BaseApiCodes $api_codes */
		$api_codes = $this->getApiCodesClassName();
		$codes = $api_codes::getApiCodeConstants();
		foreach ($codes as $name => $val) {
			if (\in_array($name, $const_to_ignore, true)) {
				$this->assertTrue(true);
				continue;
			}

			if (\in_array($name, $const_now_method, true)) {
				$name = \str_replace('_OFFSET', '', $name);
				$val = BaseApiCodes::$name();
			}
			$msg = \sprintf("Value of '{$name}' ({$val}) is out of allowed range %d-%d",
				$api_codes::getMinCode(), $api_codes::getMaxCode());

			$this->assertTrue($api_codes::isCodeValid($val), $msg);
		}
	}

	/**
	 * Checks if all defined Api code constants' values are unique.
	 *
	 * @return void
	 */
	public function testIfAllApiValuesAreUnique(): void
	{
		/** @var BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();
		$items = \array_count_values($api_codes_class_name::getMap());
		foreach ($items as $code => $count) {
			$this->assertLessThanOrEqual($count, 1, sprintf("Value of  '{$code}' is not unique. Used {$count} times."));
		}
	}

	/**
	 * Checks if all codes are mapped to existing locale strings.
	 *
	 * @return void
	 */
	public function testIfAllCodesAreCorrectlyMapped(): void
	{
		/** @var BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();
		$map = $api_codes_class_name::getMap();
		foreach ($map as $code => $mapping) {
			$str = \Lang::get($mapping);
			$this->assertNotEquals($mapping, $str,
				\sprintf('No lang entry for: %s referenced by %s', $mapping, $this->resolveConstantFromCode($code))
			);
		}
	}

	/**
	 * Tests if "classes" config entries are properly set and use at least
	 * mandatory configuration elements.
	 *
	 * @return void
	 */
	public function testConfigClassesMappingEntriesMandatoryKeys(): void
	{
		$classes = \Config::get(RB::CONF_KEY_CONVERTER_CLASSES) ?? [];
		if (\count($classes) === 0) {
			// to make PHPUnit not complaining about no assertion.
			$this->assertTrue(true);

			return;
		}

		$mandatory_keys = [
			RB::KEY_HANDLER,
		];
		foreach ($classes as $class_name => $class_config) {
			foreach ($mandatory_keys as $key_name) {
				/** @var TestCase $this */
				$this->assertArrayHasKey($key_name, $class_config);
			}
		}
	}

	/**
	 * Tests if "classes" config entries properly set, which means we look for any
	 * unknown/unsupported configuration key.
	 *
	 * @return void
	 */
	public function testConfigClassesMappingEntriesUnwantedConfigKeys(): void
	{
		$classes = \Config::get(RB::CONF_KEY_CONVERTER_CLASSES, []);
		if (\count($classes) === 0) {
			// to make PHPUnit not complaining about no assertion.
			$this->assertTrue(true);

			return;
		}

		foreach ($classes as $class_name => $class_config) {
			foreach ($class_config as $cfg_key => $cfg_val) {
				switch ($cfg_key) {
					case RB::KEY_KEY:
						if (\is_string($cfg_val)) {
							$this->assertIsString($cfg_val);
							$this->assertNotEmpty(trim($cfg_val));
						} elseif ($cfg_val !== null) {
							$this->fail(
								\sprintf("Value for key '{$cfg_key}' in '{$class_name}' must be string or null (%s found)", \gettype($cfg_key)));
						}
						break;
					case RB::KEY_HANDLER:
						$this->assertIsString($cfg_val);
						$this->assertNotEmpty(trim($cfg_val));
						break;
					case RB::KEY_PRI:
						$this->assertIsInt($cfg_val);
						$this->assertIsNumeric($cfg_val);
						break;
					default:
						$this->fail("Unknown key '{$cfg_key}' in '{$class_name}' data conversion config.");
				}
			}
		}

		$supported_keys = [
			RB::KEY_KEY,
			RB::KEY_PRI,
			RB::KEY_HANDLER,
		];
		foreach ($classes as $class_name => $class_config) {
			foreach ($class_config as $cfg_key => $cfg_val) {
				$msg = "Unknown key '{$cfg_key}' in '{$class_name}' data conversion config.";
				$this->assertContains($cfg_key, $supported_keys, $msg);
			}
		}
	}


} // end of ApiCodesTests trait
