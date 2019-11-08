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
class ResponseBuilder
{
    /**
     * Default HTTP code to be used with success responses
     *
     * @var int
     */
    public const DEFAULT_HTTP_CODE_OK = HttpResponse::HTTP_OK;

    /**
     * Default HTTP code to be used with error responses
     *
     * @var int
     */
    public const DEFAULT_HTTP_CODE_ERROR = HttpResponse::HTTP_BAD_REQUEST;

    /**
     * Min allowed HTTP code for errorXXX()
     *
     * @var int
     */
    public const ERROR_HTTP_CODE_MIN = 400;

    /**
     * Max allowed HTTP code for errorXXX()
     *
     * @var int
     */
    public const ERROR_HTTP_CODE_MAX = 599;

    /**
     * Configuration keys
     */
    public const CONF_CONFIG                     = 'response-builder';
    public const CONF_KEY_DEBUG_DEBUG_KEY        = self::CONF_CONFIG . '.debug.debug_key';
    public const CONF_KEY_DEBUG_EX_TRACE_ENABLED = self::CONF_CONFIG . '.debug.exception_handler.trace_enabled';
    public const CONF_KEY_DEBUG_EX_TRACE_KEY     = self::CONF_CONFIG . '.debug.exception_handler.trace_key';
    public const CONF_KEY_MAP                    = self::CONF_CONFIG . '.map';
    public const CONF_KEY_ENCODING_OPTIONS       = self::CONF_CONFIG . '.encoding_options';
    public const CONF_KEY_CLASSES                = self::CONF_CONFIG . '.classes';
    public const CONF_KEY_MIN_CODE               = self::CONF_CONFIG . '.min_code';
    public const CONF_KEY_MAX_CODE               = self::CONF_CONFIG . '.max_code';
    public const CONF_KEY_EXCEPTION_HANDLER      = self::CONF_CONFIG . '.exception_handler';

    /**
     * Default keys to be used by exception handler while adding debug information
     */
    public const KEY_DEBUG   = 'debug';
    public const KEY_TRACE   = 'trace';
    public const KEY_CLASS   = 'class';
    public const KEY_FILE    = 'file';
    public const KEY_LINE    = 'line';
    public const KEY_KEY     = 'key';
    public const KEY_METHOD  = 'method';
    public const KEY_SUCCESS = 'success';
    public const KEY_CODE    = 'code';
    public const KEY_LOCALE  = 'locale';
    public const KEY_MESSAGE = 'message';
    public const KEY_DATA    = 'data';

    /**
     * Default key to be used by exception handler while processing ValidationException
     * to return all the error messages
     *
     * @var string
     */
    public const KEY_MESSAGES = 'messages';

    /**
     * Default JSON encoding options. Must be specified as final value (i.e. 271) and NOT
     * PHP expression i.e. `JSON_HEX_TAG|JSON_HEX_APOS|...` as such syntax is not yet supported
     * by PHP.
     *
     * 271 = JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE
     *
     * @var int
     */
    public const DEFAULT_ENCODING_OPTIONS = 271;


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
        return Builder::success($api_code)
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
        return Builder::success($api_code)
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
        return Builder::success($api_code)
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
        return Builder::success()
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
        return Builder::error($api_code)
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
        return Builder::error($api_code)
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
        return Builder::error($api_code)
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
        return Builder::error($api_code)
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
        return Builder::error($api_code)
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
     */
    public static function errorWithMessageAndDataAndDebug(int $api_code, string $message, $data,
                                                           int $http_code = null, int $json_opts = null,
                                                           array $debug_data = null): HttpResponse
    {
        return Builder::error($api_code)
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
        return Builder::error($api_code)
            ->withMessage($message)
            ->withHttpCode($http_code)
            ->build();
    }

}
