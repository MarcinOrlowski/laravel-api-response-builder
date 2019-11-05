<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Exception handler using ResponseBuilder to return JSON even in such hard tines
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Exception;
use Illuminate\Auth\AuthenticationException as AuthException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ExceptionHandlerHelper
 */
class ExceptionHandlerHelper
{
    /**
     * Render an exception into valid API response.
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Exception               $ex      Exception
     *
     * @return HttpResponse
     */
    public static function render(/** @scrutinizer ignore-unused */ $request, Exception $ex): HttpResponse
    {
        $result = null;
        $cfg = self::getExceptionHandlerConfig();

        if ($ex instanceof HttpException) {
            // Check if we have any exception configuration for this particular Http status code.
            $ex_cfg = $cfg['http_exception'][ $ex->getStatusCode() ] ?? null;
            if (is_array($ex_cfg)) {
                $api_code = $ex_cfg['api_code'] ?? BaseApiCodes::EX_UNCAUGHT_EXCEPTION();
                $http_code = $ex_cfg['http_code'] ?? ResponseBuilder::DEFAULT_HTTP_CODE_ERROR;
                $result = static::error($ex, $api_code, $http_code);
            } else {
                // No dedicated config entry for this code, let's fall back to default handler
                $ex_cfg = $cfg['http_exception']['default'];
                $result = static::error($ex, $ex_cfg['api_code'], $ex_cfg['http_code']);
            }
        } elseif ($ex instanceof ValidationException) {
            $ex_cfg = $cfg['http_exception'][ HttpResponse::HTTP_UNPROCESSABLE_ENTITY ];
            $result = static::error($ex, $ex_cfg['api_code'], $ex_cfg['http_code']);
        }

        if ($result === null) {
            $ex_cfg = $cfg['default'];
            $result = static::error($ex, $ex_cfg['api_code'], $ex_cfg['http_code']);
        }

        return $result;
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request                 $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     *
     * @return HttpResponse
     */
    protected function unauthenticated(/** @scrutinizer ignore-unused */ $request,
                                                                         AuthException $exception): HttpResponse
    {
        $cfg = static::getExceptionHandlerConfig();
        $api_code = $cfg['http_exception'][ HttpResponse::HTTP_UNAUTHORIZED ]['api_code'];
        $http_code = $cfg['http_exception'][ HttpResponse::HTTP_UNAUTHORIZED ]['http_code'];

        return static::error($exception, $api_code, $http_code);
    }

    /**
     * Process single error and produce valid API response.
     *
     * @param Exception $ex Exception to be handled.
     * @param integer   $api_code
     * @param integer   $http_code
     *
     * @return HttpResponse
     */
//    protected static function error(Exception $ex, $exception_config_key): HttpResponse
    protected static function error(Exception $ex,
                                    int $api_code, int $http_code = null): HttpResponse
    {
        $ex_http_code = ($ex instanceof HttpException) ? $ex->getStatusCode() : $ex->getCode();
        $http_code = $http_code ?? $ex_http_code;

        // Check if we now have valid HTTP error code for this case or need to make one up. We cannot
        // throw any exception if codes are invalid because we are in Exception Handler already.
        if ($http_code < ResponseBuilder::ERROR_HTTP_CODE_MIN) {
            // not a valid code, let's try to get the exception status
            $http_code = $ex_http_code;
        }
        // can it be considered valid HTTP error code?
        if ($http_code < ResponseBuilder::ERROR_HTTP_CODE_MIN) {
            $http_code = ResponseBuilder::DEFAULT_HTTP_CODE_ERROR;
        }

        // let's build the error message
        $error_message = $ex->getMessage();

        $placeholders = [
            'api_code' => $api_code,
            'message'  => '',
        ];

        // Check if we have dedicated HTTP Code message for this type of HttpException and its status code.
        if (($error_message === '') && ($ex instanceof HttpException)) {
            $error_message = Lang::get("response-builder::builder.http_{$ex_http_code}", $placeholders);
        }

        // still nothing? if we do not have any error_message in the hand yet, we need to fall back to
        // built-in generic message for this type of exception
        if ($error_message === '') {
            $key = BaseApiCodes::getCodeMessageKey(($ex instanceof HttpException)
                ? BaseApiCodes::EX_HTTP_EXCEPTION() : BaseApiCodes::NO_ERROR_MESSAGE());
            $error_message = Lang::get($key, $placeholders);
        }

        // if we have trace data debugging enabled, let's gather some debug info and add to the response.
        $trace_data = null;
        if (Config::get(ResponseBuilder::CONF_KEY_DEBUG_EX_TRACE_ENABLED, false)) {
            $trace_data = [
                Config::get(ResponseBuilder::CONF_KEY_DEBUG_EX_TRACE_KEY, ResponseBuilder::KEY_TRACE) => [
                    ResponseBuilder::KEY_CLASS => get_class($ex),
                    ResponseBuilder::KEY_FILE  => $ex->getFile(),
                    ResponseBuilder::KEY_LINE  => $ex->getLine(),
                ],
            ];
        }

        // if this is ValidationException, add all the messages from MessageBag to the data node.
        $data = null;
        if ($ex instanceof ValidationException) {
            /** @var ValidationException $ex */
            $data = [ResponseBuilder::KEY_MESSAGES => $ex->validator->errors()->messages()];
        }

        return ResponseBuilder::errorWithMessageAndDataAndDebug($api_code, $error_message, $data,
            $http_code, null, $trace_data);
    }

    protected static function getExceptionHandlerBaseConfig(): array
    {
        return [
            'http_exception'     => [
                HttpResponse::HTTP_NOT_FOUND            => [
                    'api_code'  => BaseApiCodes::EX_HTTP_NOT_FOUND(),
                    'http_code' => HttpResponse::HTTP_NOT_FOUND,
                    'msg'       => 'response-builder::builder.http_404',
                ],
                HttpResponse::HTTP_SERVICE_UNAVAILABLE  => [
                    'api_code'  => BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE(),
                    'http_code' => HttpResponse::HTTP_SERVICE_UNAVAILABLE,
                    'msg'       => 'response-builder::builder.http_http_503',
                ],

                // used by unauthenticated() to obtain api and http code for the exception
                HttpResponse::HTTP_UNAUTHORIZED         => [
                    'api_code'  => BaseApiCodes::EX_AUTHENTICATION_EXCEPTION(),
                    'http_code' => HttpResponse::HTTP_UNAUTHORIZED,
                    'msg'       => 'response-builder::builder.http_401',
                ],

                HttpResponse::HTTP_UNPROCESSABLE_ENTITY => [
                    'api_code'  => BaseApiCodes::EX_VALIDATION_EXCEPTION(),
                    'http_code' => HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
                    'msg'       => 'response-builder::builder.http_422',
                ],
                'default'                               => [
                    'api_code'  => BaseApiCodes::EX_HTTP_EXCEPTION(),
                    'msg'       => 'response-builder::builder.http_exception',
                    'http_code' => HttpResponse::HTTP_BAD_REQUEST,
                ],
            ],
            'default' => [
                'api_code'  => BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
                'http_code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                'msg'       => 'response-builder::builder.uncaught_exception',
            ],
//            'validation_exception' => [
//                'api_code'  => BaseApiCodes::EX_VALIDATION_EXCEPTION(),
//                'http_code' => HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
//                'msg'       => 'response-builder::builder.http_422',
//            ],
        ];
    }

    protected static function getExceptionHandlerConfig(): array
    {
        return Config::get(ResponseBuilder::CONF_EXCEPTION_HANDLER_KEY, []) + self::getExceptionHandlerBaseConfig();
    }
}
