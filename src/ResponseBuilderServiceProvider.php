<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

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

/**
 * Disable return type hint inspection as we do not have it specified in that
 * class for a purpose. The base class is also not having return type hints.
 *
 * @noinspection RAeturnTypeCanBeDeclaredInspection
 */

use Illuminate\Support\ServiceProvider;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompleteConfigurationException;

/**
 * Laravel's service provider for ResponseBuilder
 *
 * @package MarcinOrlowski\ResponseBuilder
 */
class ResponseBuilderServiceProvider extends ServiceProvider
{
	protected $config_files = [
		'response_builder.php',
	];

	/**
	 * Register bindings in the container.
	 *
	 * @return void
	 */
	public function register()
	{
		foreach ($this->config_files as $file) {
			$this->mergeConfigFrom(__DIR__ . "/../config/{$file}", ResponseBuilder::CONF_CONFIG);
		}
	}

	/**
	 * Sets up package resources
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->loadTranslationsFrom(__DIR__ . '/lang', 'response-builder');

		foreach ($this->config_files as $file) {
			$this->publishes([__DIR__ . "/../config/{$file}" => config_path($file)]);
		}
	}

	/**
	 * Merge the given configuration with the existing configuration.
	 *
	 * @param string $path
	 * @param string $key
	 *
	 * @throws \MarcinOrlowski\ResponseBuilder\Exceptions\IncompleteConfigurationException
	 *
	 * @return void
	 */
	protected function mergeConfigFrom($path, $key)
	{
		$defaults = require $path;
		$config = $this->app['config']->get($key, []);

		$merged_config = Util::mergeConfig($defaults, $config);

		if (!isset($merged_config['converter']['classes'])) {
			throw new IncompleteConfigurationException(
				sprintf('Configuration lacks "%s" array.', ResponseBuilder::CONF_KEY_CONVERTER_CLASSES));
		}

		Util::sortArrayByPri($merged_config['converter']['classes']);

        $this->app['config']->set($key, $merged_config);
    }

}
