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

use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
	/**
	 * @var HttpResponse
	 */
	protected $response;

	use Traits\TestingHelpers;
	use \MarcinOrlowski\PhpunitExtraAsserts\Traits\ExtraAsserts;

	/**
	 * Define environment setup.
	 *
	 * @param \Illuminate\Foundation\Application $app
	 *
	 * @return void
	 */
	public function getEnvironmentSetUp($app)
	{
		// redirect all debug logs to stderr
		// Enable by
		Config::set(RB::CONF_KEY_DEBUG_CONVERTER_DEBUG_ENABLED, true);

		// use 'stderr' channer to see the log output (if needed).
		$app['config']->set('logging.default', 'null');
	}

	/**
	 * Returns ApiCodes class name. We need that done this way, so you can easily plug-and-play
	 * out testing trait into your project.
	 *
	 * @return string
	 */
	public function getApiCodesClassName(): string
	{
		return \MarcinOrlowski\ResponseBuilder\BaseApiCodes::class;
	}

	/**
	 * [Orchestra] Load service providers we need during the tests
	 *
	 * @param \Illuminate\Foundation\Application $app
	 *
	 * @return array
	 */
	protected function getPackageProviders($app): array
	{
		return [
			\MarcinOrlowski\ResponseBuilder\ResponseBuilderServiceProvider::class,
		];
	}
}
