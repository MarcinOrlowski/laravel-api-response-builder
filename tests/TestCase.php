<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

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
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @var HttpResponse
     */
    protected $response;

    use Traits\TestingHelpers;

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
            \MarcinOrlowski\ResponseBuilder\Tests\Providers\ResponseBuilderServiceProvider::class,
        ];
    }
}
