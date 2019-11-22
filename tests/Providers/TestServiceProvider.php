<?php

namespace MarcinOrlowski\ResponseBuilder\Tests\Providers;

use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

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

/**
 * Class TestServiceProvider
 *
 * We need slightly different paths for the test environment, therefore we cannot
 * use original ResponseBuilderServiceProvider. Additionally, we do not need support
 * for config publishing.
 */
class TestServiceProvider extends \MarcinOrlowski\ResponseBuilder\ResponseBuilderServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->config_files as $file) {
            $this->mergeConfigFrom(__DIR__ . "/../../config/{$file}", ResponseBuilder::CONF_CONFIG);
        }
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
}
