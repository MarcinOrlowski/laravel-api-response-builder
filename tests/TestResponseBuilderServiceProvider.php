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

/**
 * Class TestResponseBuilderServiceProvider
 *
 * We need slightly different paths for the test environment, so we cannot
 * use original ResponseBuilderServiceProvider
 */
class TestResponseBuilderServiceProvider extends ResponseBuilderServiceProvider
{
	/**
	 * Sets up package resources
	 *
	 * @return void
	 */
	public function boot()
	{
		parent::boot();

		$this->loadTranslationsFrom(__DIR__ . '/../src/lang', 'response-builder');
	}
}
