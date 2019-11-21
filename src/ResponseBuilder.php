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
class ResponseBuilder extends ResponseBuilderBase
{
    /**
     * Returns success
     *
     * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
     *                                         of the JSON response, single supported object or @null if there's no
     *                                         to be returned.
     * @param integer|null      $api_code      API code to be returned or @null to use value of BaseApiCodes::OK().
     * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
     *                                         substitution or @null if none.
     * @param integer|null      $http_code     HTTP code to be used for HttpResponse sent or @null
     *                                         for default DEFAULT_HTTP_CODE_OK.
     * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
     *                                         options or pass @null to use value from your config (or defaults).
     *
     * @return HttpResponse
     */
    public static function success($data = null, $api_code = null, array $placeholders = null,
                                   int $http_code = null, int $json_opts = null): HttpResponse
    {
        return Builder::asSuccess($api_code)
            ->withData($data)
            ->withPlaceholders($placeholders)
            ->withHttpCode($http_code)
            ->withJsonOptions($json_opts)
            ->build();
    }

    /**
     * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error
     * message uses placeholders as well as return data payload
     *
     * @param integer           $api_code      Your API code to be returned with the response object.
     * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
     *                                         substitution or @null if none.
     * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
     *                                         of the JSON response, single supported object or @null if there's no
     *                                         to be returned.
     * @param integer|null      $http_code     HTTP code to be used for HttpResponse sent or @null
     *                                         for default DEFAULT_HTTP_CODE_ERROR.
     * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
     *                                         options or pass @null to use value from your config (or defaults).
     *
     * @return HttpResponse
     */
    public static function error(int $api_code, array $placeholders = null, $data = null, int $http_code = null,
                                 int $json_opts = null): HttpResponse
    {
        return Builder::asError($api_code)
            ->withPlaceholders($placeholders)
            ->withData($data)
            ->withHttpCode($http_code)
            ->withJsonOptions($json_opts)
            ->build();
    }
}
