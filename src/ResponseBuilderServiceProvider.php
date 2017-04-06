<?php

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\ServiceProvider;

/**
 * Class ResponseBuilderServiceProvider
 */
class ResponseBuilderServiceProvider extends ServiceProvider
{
	/**
	 * Register bindings in the container.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__.'/../config/response_builder.php', 'response_builder'
		);
	}

	/**
	 * Sets up package resources
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->loadTranslationsFrom(__DIR__ . '/lang', 'response-builder');

		$source = __DIR__ . '/../config/response_builder.php';
		$this->publishes([
			$source => config_path('response_builder.php'),
		]);
	}
}
