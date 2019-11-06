<?php
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

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;


/**
 * Builds standardized HttpResponse response object
 */
class ResponseBuilderProxy extends ResponseBuilder
{
    /**
     * Proxy method to expose protected buildSuccessResponse()
     *
     * @noinspection MoreThanThreeArgumentsInspection
     */
    public static function buildSuccessResponse($data = null, int $api_code = null, array $placeholders = null,
                                                int $http_code = null, int $json_opts = null): HttpResponse
    {
        return parent::buildSuccessResponse($data, $api_code, $placeholders, $http_code, $json_opts);
    }
}
