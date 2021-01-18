<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

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
use MarcinOrlowski\ResponseBuilder\Type;

/**
 * Tests InvalidTypeException class
 */
class InvalidTypeExceptionTest extends TestCase
{
	/**
	 * Checks if InvalidTypeException's constructor correctly deals with
	 * empty array of allowed types and fails.
	 */
	public function testX(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$ex = new Ex\InvalidTypeException('foo', Type::STRING, []);
	}

}
