<?php

namespace MarcinOrlowski\ResponseBuilder\Tests\Providers;

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

/**
 * Class TestResponseBuilderServiceProvider
 *
 * We need slightly different paths for the test environment, so we cannot
 * use original ResponseBuilderServiceProvider
 */
class ResponseBuilderServiceProvider extends \MarcinOrlowski\ResponseBuilder\ResponseBuilderServiceProvider
{
	/**
	 * Register bindings in the container.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__ . '/../../config/response_builder.php', 'response_builder'
		);
	}

	/**
	 * Sets up package resources
	 *
	 * @return void
	 */
	public function boot()
	{
		parent::boot();

		$this->loadTranslationsFrom(__DIR__ . '/../../src/lang', 'response-builder');
	}

	/** *******************************************************************************************
	 * Support for multi-dimensional config array. Built-in config merge only supports flat arrays.
	 *
	 */

	/**
	 * Merge the given configuration with the existing configuration.
	 *
	 * @param string $path
	 * @param string $key
	 *
	 * @return void
	 */
	protected function mergeConfigFrom($path, $key)
	{
		$config = $this->app['config']->get($key, []);
		$this->app['config']->set($key, $this->mergeConfig(require $path, $config));
	}

	/**
	 * Merges the configs together and takes multi-dimensional arrays into account.
	 *
	 * @param array $original
	 * @param array $merging
	 *
	 * @return array
	 */
	protected function mergeConfig(array $original, array $merging)
	{
		$array = array_merge($original, $merging);
		foreach ($original as $key => $value) {
			if (!is_array($value)) {
				continue;
			}
			if (!Arr::exists($merging, $key)) {
				continue;
			}
			if (is_numeric($key)) {
				continue;
			}
			$array[ $key ] = $this->mergeConfig($value, $merging[ $key ]);
		}

		return $array;
	}
}
