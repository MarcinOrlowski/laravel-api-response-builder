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
abstract class ResponseBuilderTestCaseBase extends \Orchestra\Testbench\TestCase
{
	use \MarcinOrlowski\ResponseBuilder\Tests\Traits\ResponseBuilderTestHelper;

	/**
	 * @return ApiCodeBase
	 */
	public function getApiCodesObject()
	{
		return new \MarcinOrlowski\ResponseBuilder\ApiCodeBase();
	}

	/**
	 * @return string
	 */
	public function getApiCodesClassName()
	{
		return \MarcinOrlowski\ResponseBuilder\ApiCodeBase::class;
	}

}
