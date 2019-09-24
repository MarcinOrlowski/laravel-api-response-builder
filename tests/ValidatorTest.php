<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

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

use MarcinOrlowski\ResponseBuilder\Validator;

class ValidatorTest extends TestCase
{
	public function testAssertString(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		Validator::assertInt(123, 'abc');
	}

	public function testAssertInt(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		Validator::assertInt(__FUNCTION__, 'chicken');
	}

	// check if assertIntRange() main variable type is ensured to be integer.
	public function testAssertIntRangeVarType(): void
	{
		// ensure main variable is an integer
		$this->expectException(\InvalidArgumentException::class);
		Validator::assertIntRange(__FUNCTION__, 'string', 100, 200);
	}

	// check if assertIntRange() range $min and $max are in right order.
	public function testAssertIntRangeMinMaxOrder(): void
	{
		// ensure main variable is an integer
		$this->expectException(\RuntimeException::class);
		Validator::assertIntRange(__FUNCTION__, 300, 500, 200);
	}

	// check if assertIntRange() to ensure we check $var is in range
	// and $max bounds only
	public function testAssertIntRangeVarInMinMaxRange(): void
	{
		// ensure main variable is an integer
		$this->expectException(\InvalidArgumentException::class);
		Validator::assertIntRange(__FUNCTION__, 100, 300, 500);
	}

}