<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Class TestCase
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @var HttpResponse
     */
    protected $response;

    use Traits\TestingHelpers;

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     *
     * NOTE: not return typehint due to compatibility with TestBench's method signature.
     *
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getEnvironmentSetUp($app)
    {
        // redirect all debug logs to stderr
        // Enable by
        Config::set(RB::CONF_KEY_DEBUG_CONVERTER_DEBUG_ENABLED, true);

        // use 'stderr' channer to see the log output (if needed).
        /** @noinspection OffsetOperationsInspection */
        $app['config']->set('logging.default', 'null');
    }

    /**
     * Returns ApiCodes class name. We need that done this way, so you can easily plug-and-play
     * out testing trait into your project.
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
     * @return array<int, class-string>
     *
     * NOTE: not return typehint due to compatibility with TestBench's method signature.
     *
     * @noinspection PhpUnusedParameterInspection
     * @noinspection PhpMissingParamTypeInspection
     *
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
     */
    protected function getPackageProviders($app): array
    {
        return [
            \MarcinOrlowski\ResponseBuilder\ResponseBuilderServiceProvider::class,
        ];
    }

} // end of class
