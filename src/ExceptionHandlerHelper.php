<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Exception handler using ResponseBuilder to return JSON even in such hard tines
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2020 Marcin Orlowski
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
        $cfg = static::getExceptionHandlerConfig()['map'];

        if ($ex instanceof HttpException) {
            // Check if we have any exception configuration for this particular HTTP status code.
            // This confing entry is guaranted to exist (at least 'default'). Enforced by tests.
            $http_code = $ex->getStatusCode();
            $ex_cfg = $cfg[ HttpException::class ][ $http_code ] ?? null;
            $ex_cfg = $ex_cfg ?? $cfg[ HttpException::class ]['default'];
            $result = self::processException($ex, /** @scrutinizer ignore-type */ $ex_cfg, $http_code);
        } elseif ($ex instanceof ValidationException) {
            // This entry is guaranted to exist. Enforced by tests.
            $http_code = HttpResponse::HTTP_UNPROCESSABLE_ENTITY;
            $result = self::processException($ex, $cfg[ HttpException::class ][ $http_code ], $http_code);
        }

        if ($result === null) {
            // This entry is guaranted to exist. Enforced by tests.
            $result = self::processException($ex, $cfg['default'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $result;
    }

    /**
     * Handles given exception and produces valid HTTP response object.
     *
     * @param \Exception $ex                 Exception to be handled.
     * @param array      $ex_cfg             ExceptionHandler's config excerpt related to $ex exception type.
     * @param int        $fallback_http_code HTTP code to be assigned to produced $ex related response in
     *                                       case configuration array lacks own `http_code` value.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected static function processException(\Exception $ex, array $ex_cfg, int $fallback_http_code)
    {
        $api_code = $ex_cfg['api_code'];
        $http_code = $ex_cfg['http_code'] ?? $fallback_http_code;
        $msg_key = $ex_cfg['msg_key'] ?? null;
        $msg_enforce = $ex_cfg['msg_enforce'] ?? false;

        // No message key, let's get exception message and if there's nothing useful, fallback to built-in one.
        $msg = $ex->getMessage();
        $placeholders = [
            'api_code' => $api_code,
            'message'  => ($msg !== '') ? $msg : '???',
        ];

        // shall we enforce error message?
        if ($msg_enforce) {
            // yes, please.
            if ($msg_key === null) {
                // there's no msg_key configured for this exact code, so let's obtain our default message
                $msg = ($msg_key === null) ? static::getErrorMessageForException($ex, $http_code, $placeholders)
                    : Lang::get($msg_key, $placeholders);
            }
        } else {
            // nothing enforced, handling pipeline: ex_message -> user_defined_msg -> http_ex -> default
            if ($msg === '') {
                $msg = ($msg_key === null) ? static::getErrorMessageForException($ex, $http_code, $placeholders)
                    : Lang::get($msg_key, $placeholders);
            }
        }

        // Lets' try to build the error response with what we have now
        return static::error($ex, $api_code, $http_code, $msg);
    }

    /**
     * Returns error message for given exception. If exception message is empty, then falls back to
     * `default` handler either for HttpException (if $ex is instance of it), or generic `default`
     * config.
     *
     * @param \Exception $ex
     * @param int        $http_code
     * @param array      $placeholders
     *
     * @return string
     */
    protected static function getErrorMessageForException(\Exception $ex, int $http_code, array $placeholders): string
    {
        // exception message is uselss, lets go deeper
        if ($ex instanceof HttpException) {
            $error_message = Lang::get("response-builder::builder.http_{$http_code}", $placeholders);
        } else {
            // Still got nothing? Fall back to built-in generic message for this type of exception.
            $key = BaseApiCodes::getCodeMessageKey(($ex instanceof HttpException)
                ? /** @scrutinizer ignore-deprecated */ BaseApiCodes::EX_HTTP_EXCEPTION()
                : /** @scrutinizer ignore-deprecated */ BaseApiCodes::NO_ERROR_MESSAGE());
            $error_message = Lang::get($key, $placeholders);
        }

        return $error_message;
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
        // This entry is guaranted to exist. Enforced by tests.
        $http_code = HttpResponse::HTTP_UNAUTHORIZED;
        $cfg = static::getExceptionHandlerConfig()['map'][ HttpException::class ][ $http_code ];

        return static::processException($exception, $cfg, $http_code);
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
    protected static function error(Exception $ex,
                                    int $api_code, int $http_code = null, string $error_message): HttpResponse
    {
        $ex_http_code = ($ex instanceof HttpException) ? $ex->getStatusCode() : $ex->getCode();
        $http_code = $http_code ?? $ex_http_code;

        // Check if we now have valid HTTP error code for this case or need to make one up.
        // We cannot throw any exception if codes are invalid because we are in Exception Handler already.
        if ($http_code < ResponseBuilder::ERROR_HTTP_CODE_MIN) {
            // Not a valid code, let's try to get the exception status.
            $http_code = $ex_http_code;
        }
        // Can it be considered a valid HTTP error code?
        if ($http_code < ResponseBuilder::ERROR_HTTP_CODE_MIN) {
            // We now handle uncaught exception, so we cannot throw another one if there's
            // something wrong with the configuration, so we try to recover and use built-in
            // codes instead.
            // FIXME: We should log this event as (warning or error?)
            $http_code = ResponseBuilder::DEFAULT_HTTP_CODE_ERROR;
        }

        // If we have trace data debugging enabled, let's gather some debug info and add to the response.
        $debug_data = null;
        if (Config::get(ResponseBuilder::CONF_KEY_DEBUG_EX_TRACE_ENABLED, false)) {
            $debug_data = [
                Config::get(ResponseBuilder::CONF_KEY_DEBUG_EX_TRACE_KEY, ResponseBuilder::KEY_TRACE) => [
                    ResponseBuilder::KEY_CLASS => get_class($ex),
                    ResponseBuilder::KEY_FILE  => $ex->getFile(),
                    ResponseBuilder::KEY_LINE  => $ex->getLine(),
                ],
            ];
        }

        // If this is ValidationException, add all the messages from MessageBag to the data node.
        $data = null;
        if ($ex instanceof ValidationException) {
            /** @var ValidationException $ex */
            $data = [ResponseBuilder::KEY_MESSAGES => $ex->validator->errors()->messages()];
        }

        return ResponseBuilder::asError($api_code)
            ->withMessage($error_message)
            ->withHttpCode($http_code)
            ->withData($data)
            ->withDebugData($debug_data)
            ->build();
    }

    /**
     * Returns default (built-in) exception handler config array.
     *
     * @return array
     */
    protected static function getExceptionHandlerDefaultConfig(): array
    {
        return [
            'map' => [
                HttpException::class => [
                    // used by unauthenticated() to obtain api and http code for the exception
                    HttpResponse::HTTP_UNAUTHORIZED         => [
                        'api_code' => /** @scrutinizer ignore-deprecated */ BaseApiCodes::EX_AUTHENTICATION_EXCEPTION(),
                    ],
                    // Required by ValidationException handler
                    HttpResponse::HTTP_UNPROCESSABLE_ENTITY => [
                        'api_code' => /** @scrutinizer ignore-deprecated */ BaseApiCodes::EX_VALIDATION_EXCEPTION(),
                    ],
                    // default handler is mandatory. `default` entry MUST have both `api_code` and `http_code` set.
                    'default'                               => [
                        'api_code'  => /** @scrutinizer ignore-deprecated */ BaseApiCodes::EX_HTTP_EXCEPTION(),
                        'http_code' => HttpResponse::HTTP_BAD_REQUEST,
                    ],
                ],
                // default handler is mandatory. `default` entry MUST have both `api_code` and `http_code` set.
                'default'            => [
                    'api_code'  => /** @scrutinizer ignore-deprecated */ BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
                    'http_code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                ],
            ],
        ];
    }

    /**
     * Returns ExceptionHandlerHelper configration array with user configuration merged into built-in defaults.
     *
     * @return array
     */
    protected static function getExceptionHandlerConfig(): array
    {
        return Util::mergeConfig(static::getExceptionHandlerDefaultConfig(),
            \Config::get(ResponseBuilder::CONF_KEY_EXCEPTION_HANDLER, []));
    }

}
