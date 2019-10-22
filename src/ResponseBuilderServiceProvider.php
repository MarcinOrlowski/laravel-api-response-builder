<?php
/**
 * Disable return type hint inspection as we do not have it specified in that
 * class for a purpose. The base class is also not having return type hints.
 *
 * @noinspection ReturnTypeCanBeDeclaredInspection
 */

declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\ServiceProvider;

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
			__DIR__ . '/../config/response_builder.php', 'response_builder'
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

	/**********************************************************************************************
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
			if (!array_key_exists($key, $merging)) {
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
