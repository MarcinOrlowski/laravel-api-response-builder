<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PhpDocMissingThrowsInspection
 */
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Builder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class FactoryTest
 */
class FactoryTest extends TestCase
{
	/**
	 * Checks if asSuccess() properly returns object of extending class
	 */
	public function testAsSuccess(): void
	{
		$dummy_rb = DummyResponseBuilder::asSuccess();
        $this->assertEquals(DummyResponseBuilder::class, \get_class($dummy_rb));
	}

	/**
	 * Checks if asError(); properly returns object of extending class
	 */
	public function testAsError(): void
	{
		$dummy_rb = DummyResponseBuilder::asError(BaseApiCodes::NO_ERROR_MESSAGE());
        $this->assertEquals(DummyResponseBuilder::class, \get_class($dummy_rb));
	}

} // end of class
