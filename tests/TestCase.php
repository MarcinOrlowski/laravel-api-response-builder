<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

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
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
	use Traits\TestingHelpers;

	/**
	 * @return string
	 */
	public function getApiCodesClassName()
	{
		return \MarcinOrlowski\ResponseBuilder\BaseApiCodes::class;
	}


	// -----------------------------------------------------------

	/**
	 * [Orchestra] Load service providers we need during the tests
	 *
	 * @param \Illuminate\Foundation\Application $app
	 *
	 * @return array
	 */
	protected function getPackageProviders($app)
	{
		return [
			\MarcinOrlowski\ResponseBuilder\Tests\Providers\ResponseBuilderServiceProvider::class,
		];
	}

}
