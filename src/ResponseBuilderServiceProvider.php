<?php

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-response-builder
 */

use Illuminate\Support\ServiceProvider;

class ResponseBuilderServiceProvider extends ServiceProvider
{
	/**
	 * Register bindings in the container.
	 *
	 * @return void
	 */
	public function register() {
		// dummy
	}

	/**
	 * Sets up package resources
	 *
	 * @return void
	 */
	public function boot() {
		$this->loadTranslationsFrom(__DIR__ . '/lang', 'response_builder');

		$this->publishes([
			__DIR__ . '/../config/response_builder.php' => config_path('response_builder.php'),
		]);
	}
}