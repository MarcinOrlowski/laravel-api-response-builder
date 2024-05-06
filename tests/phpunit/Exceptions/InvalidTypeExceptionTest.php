<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Exceptions;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\Type;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

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
		new Ex\InvalidTypeException('foo', Type::STRING, []);
	}

}
