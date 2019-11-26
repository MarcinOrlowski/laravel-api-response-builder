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
class ResponseBuilderLegacy extends ResponseBuilderBase
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
        return ResponseBuilder::asSuccess($api_code)
            ->withData($data)
            ->withPlaceholders($placeholders)
            ->withHttpCode($http_code)
            ->withJsonOptions($json_opts)
            ->build();
    }

    /**
     * Returns success
     *
     * @param integer|null $api_code      API code to be returned or @null to use value of BaseApiCodes::OK().
     * @param array|null   $placeholders  Placeholders passed to Lang::get() for message placeholders
     *                                    substitution or @null if none.
     * @param integer|null $http_code     HTTP code to be used for HttpResponse sent or @null
     *                                    for default DEFAULT_HTTP_CODE_OK.
     *
     * @return HttpResponse
     *
     * @deprecated Please use Builder class.
     */
    public static function successWithCode(int $api_code = null, array $placeholders = null,
                                           int $http_code = null): HttpResponse
    {
        return ResponseBuilder::asSuccess($api_code)
            ->withPlaceholders($placeholders)
            ->withHttpCode($http_code)
            ->build();
    }

    /**
     * @param string            $message       Custom message to be returned as part of the response.
     * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
     *                                         of the JSON response, single supported object or @null if there's no
     *                                         to be returned.
     * @param integer|null      $http_code     HTTP code to be used for HttpResponse sent or @null
     *                                         for default DEFAULT_HTTP_CODE_OK.
     *
     * @return HttpResponse
     *
     * @deprecated Please use Builder class.
     */
    public static function successWithMessage(string $message, $data = null, int $api_code = null,
                                              int $http_code = null): HttpResponse
    {
        return ResponseBuilder::asSuccess($api_code)
            ->withMessage($message)
            ->withData($data)
            ->withHttpCode($http_code)
            ->build();
    }

    /**
     * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node.
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
     *
     * @deprecated Please use Builder class.
     */
    public static function successWithHttpCode(int $http_code = null): HttpResponse
    {
        return ResponseBuilder::asSuccess()
            ->withHttpCode($http_code)
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
        return ResponseBuilder::asError($api_code)
            ->withPlaceholders($placeholders)
            ->withData($data)
            ->withHttpCode($http_code)
            ->withJsonOptions($json_opts)
            ->build();
    }

    /**
     * @param integer           $api_code      Your API code to be returned with the response object.
     * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
     *                                         of the JSON response, single supported object or @null if there's no
     *                                         to be returned.
     * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
     *                                         substitution or @null if none.
     * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
     *                                         options or pass @null to use value from your config (or defaults).
     *
     * @return HttpResponse
     *
     * @deprecated Please use Builder class.
     */
    public static function errorWithData(int $api_code, $data, array $placeholders = null,
                                         int $json_opts = null): HttpResponse
    {
        return ResponseBuilder::asError($api_code)
            ->withData($data)
            ->withPlaceholders($placeholders)
            ->withJsonOptions($json_opts)
            ->build();
    }

    /**
     * @param integer           $api_code      Your API code to be returned with the response object.
     * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
     *                                         of the JSON response, single supported object or @null if there's no
     *                                         to be returned.
     * @param integer           $http_code     HTTP code to be used for HttpResponse sent.
     * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
     *                                         substitution or @null if none.
     * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
     *                                         options or pass @null to use value from your config (or defaults).
     *
     * @return HttpResponse
     *
     * @throws \InvalidArgumentException if http_code is @null
     *
     * @deprecated Please use Builder class.
     */
    public static function errorWithDataAndHttpCode(int $api_code, $data, int $http_code, array $placeholders = null,
                                                    int $json_opts = null): HttpResponse
    {
        return ResponseBuilder::asError($api_code)
            ->withData($data)
            ->withHttpCode($http_code)
            ->withPlaceholders($placeholders)
            ->withJsonOptions($json_opts)
            ->build();
    }

    /**
     * @param integer    $api_code     Your API code to be returned with the response object.
     * @param integer    $http_code    HTTP code to be used for HttpResponse sent or @null
     *                                 for default DEFAULT_HTTP_CODE_ERROR.
     * @param array|null $placeholders Placeholders passed to Lang::get() for message placeholders
     *                                 substitution or @null if none.
     *
     * @return HttpResponse
     *
     * @throws \InvalidArgumentException if http_code is @null
     *
     * @deprecated Please use Builder class.
     */
    public static function errorWithHttpCode(int $api_code, int $http_code, array $placeholders = null): HttpResponse
    {
        return ResponseBuilder::asError($api_code)
            ->withHttpCode($http_code)
            ->withPlaceholders($placeholders)
            ->build();
    }

    /**
     * @param integer           $api_code  Your API code to be returned with the response object.
     * @param string            $message   Custom message to be returned as part of error response
     * @param object|array|null $data      Array of primitives and supported objects to be returned in 'data' node
     *                                     of the JSON response, single supported object or @null if there's no
     *                                     to be returned.
     * @param integer|null      $http_code Optional HTTP status code to be used for HttpResponse sent
     *                                     or @null for DEFAULT_HTTP_CODE_ERROR
     * @param integer|null      $json_opts See http://php.net/manual/en/function.json-encode.php for supported
     *                                     options or pass @null to use value from your config (or defaults).
     *
     * @return HttpResponse
     *
     * @deprecated Please use Builder class.
     */
    public static function errorWithMessageAndData(int $api_code, string $message, $data,
                                                   int $http_code = null, int $json_opts = null): HttpResponse
    {
        return ResponseBuilder::asError($api_code)
            ->withMessage($message)
            ->withData($data)
            ->withHttpCode($http_code)
            ->withJsonOptions($json_opts)
            ->build();
    }

    /**
     * @param integer           $api_code   Your API code to be returned with the response object.
     * @param string            $message    custom message to be returned as part of error response
     * @param object|array|null $data       Array of primitives and supported objects to be returned in 'data' node
     *                                      of the JSON response, single supported object or @null if there's no
     *                                      to be returned.
     * @param integer|null      $http_code  HTTP code to be used for HttpResponse sent or @null
     *                                      for default DEFAULT_HTTP_CODE_ERROR.
     * @param integer|null      $json_opts  See http://php.net/manual/en/function.json-encode.php for supported
     *                                      options or pass @null to use value from your config (or defaults).
     * @param array|null        $debug_data optional debug data array to be added to returned JSON.
     *
     * @return HttpResponse
     *
     * @deprecated Please use Builder class.
     *
     * @noinspection PhpTooManyParametersInspection
     */
    public static function errorWithMessageAndDataAndDebug(int $api_code, string $message, $data,
                                                           int $http_code = null, int $json_opts = null,
                                                           array $debug_data = null): HttpResponse
    {
        return ResponseBuilder::asError($api_code)
            ->withMessage($message)
            ->withData($data)
            ->withHttpCode($http_code)
            ->withJsonOptions($json_opts)
            ->withDebugData($debug_data)
            ->build();
    }

    /**
     * @param integer      $api_code  Your API code to be returned with the response object.
     * @param string       $message   Custom message to be returned as part of error response
     * @param integer|null $http_code HTTP code to be used with final response sent or @null
     *                                for default DEFAULT_HTTP_CODE_ERROR.
     *
     * @return HttpResponse
     *
     * @deprecated Please use Builder class.
     */
    public static function errorWithMessage(int $api_code, string $message, int $http_code = null): HttpResponse
    {
        return ResponseBuilder::asError($api_code)
            ->withMessage($message)
            ->withHttpCode($http_code)
            ->build();
    }

}
