<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Auth\AuthenticationException as AuthException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use MarcinOrlowski\ResponseBuilder\Contracts\ExceptionHandlerContract;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlers\DefaultExceptionHandler;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlers\HttpExceptionHandler;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Exception handler using ResponseBuilder to return JSON even in such hard tines
 */
class ExceptionHandlerHelper
{
    /**
     * Render an exception into valid API response.
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Throwable               $ex      Throwable to handle
     *
     * @throws Ex\InvalidTypeException
     * @throws Ex\NotIntegerException
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\ConfigurationNotFoundException
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\ArrayWithMixedKeysException
     *
     * NOTE: no typehints due to compatibility with Laravel's method signature.
     *
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpUnusedParameterInspection
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
     */
    public static function render($request, \Throwable $ex): HttpResponse
    {
        $result = null;

        $cfg = static::getHandlerConfig($ex);
        do {
            if ($cfg === null) {
                // Default handler MUST be present by design and always return something useful.
                $cfg = static::getExceptionHandlerConfig()[ RB::KEY_DEFAULT ];
            }

            $handler = new $cfg[ RB::KEY_HANDLER ]();
            /**  @var ExceptionHandlerContract $handler */
            $handler_result = $handler->handle($cfg[ RB::KEY_CONFIG ], $ex);
            if ($handler_result !== null) {
                $result = static::processException($ex, $handler_result);
            } else {
                // Let's fall back to default handler in next round.
                $cfg = null;
            }
        } while ($result === null);

        return $result;
    }

    /**
     * Handles given throwable and produces valid HTTP response object.
     *
     * @param \Throwable $ex                 Throwable to be handled.
     * @param array      $ex_cfg             ExceptionHandler's config excerpt related to $ex exception type.
     * @param int        $fallback_http_code HTTP code to be assigned to produced $ex related response in
     *                                       case configuration array lacks own `http_code` value. Default
     *                                       HttpResponse::HTTP_INTERNAL_SERVER_ERROR
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * NOTE: no return typehint due to compatibility with Laravel signature.
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     *
     * @throws Ex\InvalidTypeException
     * @throws Ex\NotIntegerException
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\ConfigurationNotFoundException
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\ArrayWithMixedKeysException
     */
    protected static function processException(\Throwable $ex, array $ex_cfg,
                                               int        $fallback_http_code = HttpResponse::HTTP_INTERNAL_SERVER_ERROR)
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
            // there's no msg_key configured for this exact code, so let's obtain our default message
            $msg = ($msg_key === null) ? static::getErrorMessageForException($ex, $http_code, $placeholders)
                : Lang::get($msg_key, $placeholders);
        } else if ($msg === '') {
            // nothing enforced, handling pipeline: ex_message -> user_defined_msg -> http_ex -> default
            $msg = ($msg_key === null) ? static::getErrorMessageForException($ex, $http_code, $placeholders)
                : Lang::get($msg_key, $placeholders);
        }

        // As Lang::get() is documented to also returning arrays(?)...
        if (is_array($msg)) {
            $msg = implode('', $msg);
        }

        // Lets' try to build the error response with what we have now
        /** @noinspection PhpUnhandledExceptionInspection */
        return static::error($ex, $api_code, $http_code, $msg);
    }

    /**
     * Returns error message for given exception. If exception message is empty, then falls back to
     * `default` handler either for HttpException (if $ex is instance of it), or generic `default`
     * config.
     *
     * @param \Throwable $ex
     * @param int        $http_code
     * @param array      $placeholders
     *
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\InvalidTypeException
     * @throws Ex\NotIntegerException
     */
    protected static function getErrorMessageForException(\Throwable $ex, int $http_code,
                                                          array      $placeholders): string
    {
        // exception message is uselss, lets go deeper
        if ($ex instanceof HttpException) {
            $error_message = Lang::get("response-builder::builder.http_{$http_code}", $placeholders);
        } else {
            // Still got nothing? Fall back to built-in generic message for this type of exception.
            $http_ex_cls = HttpException::class;
            /** @var object $ex */
            $key = BaseApiCodes::getCodeMessageKey($ex instanceof $http_ex_cls
                ? BaseApiCodes::EX_HTTP_EXCEPTION() : BaseApiCodes::NO_ERROR_MESSAGE());
            // Default strings are expected to always be available.
            /** @var string $key */
            $error_message = Lang::get($key, $placeholders);
        }

        // As Lang::get() is documented to also returning arrays(?)...
        if (is_array($error_message)) {
            $error_message = implode('', $error_message);
        }

        return $error_message;
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request                 $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     *
     * @throws Ex\InvalidTypeException
     * @throws Ex\NotIntegerException
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\ConfigurationNotFoundException
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\ArrayWithMixedKeysException
     *
     * @noinspection PhpUnusedParameterInspection
     * @noinspection UnknownInspectionInspection
     *
     * NOTE: not typehints due to compatibility with Laravel's method signature.
     *
     * @noinspection PhpMissingParamTypeInspection
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
     */
    protected function unauthenticated($request, AuthException $exception): HttpResponse
    {
        $cfg = static::getExceptionHandlerConfig();

        // This config entry is guaranted to exist. Enforced by tests.
        $cfg = $cfg[ HttpException::class ][ RB::KEY_CONFIG ][ HttpResponse::HTTP_UNAUTHORIZED ];

        /**
         * NOTE: no typehint due to compatibility with Laravel signature.
         *
         * @noinspection PhpParamsInspection
         */
        return static::processException($exception, $cfg, HttpResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Process single error and produce valid API response.
     *
     * @param \Throwable  $ex Exception to be handled.
     * @param integer     $api_code
     * @param int|null    $http_code
     * @param string|null $error_message
     *
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\ConfigurationNotFoundException
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\ArrayWithMixedKeysException
     * @throws Ex\InvalidTypeException
     * @throws Ex\NotIntegerException
     */
    protected static function error(Throwable $ex, int $api_code,
                                    ?int      $http_code = null,
                                    ?string   $error_message = null): HttpResponse
    {
        $ex_http_code = ($ex instanceof HttpException) ? $ex->getStatusCode() : $ex->getCode();
        $http_code = $http_code ?? $ex_http_code;
        $error_message = $error_message ?? '';

        // Check if we now have valid HTTP error code for this case or need to make one up.
        // We cannot throw any exception if codes are invalid because we are in Exception Handler already.
        if ($http_code < RB::ERROR_HTTP_CODE_MIN) {
            // Not a valid code, let's try to get the exception status.
            $http_code = $ex_http_code;
        }
        // Can it be considered a valid HTTP error code?
        if ($http_code < RB::ERROR_HTTP_CODE_MIN) {
            // We now handle uncaught exception, so we cannot throw another one if there's
            // something wrong with the configuration, so we try to recover and use built-in
            // codes instead.
            // FIXME: We should log this event as (warning or error?)
            $http_code = RB::DEFAULT_HTTP_CODE_ERROR;
        }

        // If we have trace data debugging enabled, let's gather some debug info and add to the response.
        $debug_data = null;
        if (Config::get(RB::CONF_KEY_DEBUG_EX_TRACE_ENABLED, false)) {
            $debug_data = [
                Config::get(RB::CONF_KEY_DEBUG_EX_TRACE_KEY, RB::KEY_TRACE) => [
                    RB::KEY_CLASS => \get_class($ex),
                    RB::KEY_FILE  => $ex->getFile(),
                    RB::KEY_LINE  => $ex->getLine(),
                ],
            ];
        }

        // If this is ValidationException, add all the messages from MessageBag to the data node.
        $data = null;
        if ($ex instanceof ValidationException) {
            $data = [RB::KEY_MESSAGES => $ex->validator->errors()->messages()];
        }

        return RB::asError($api_code)
            ->withMessage($error_message)
            ->withHttpCode($http_code)
            ->withData($data)
            ->withDebugData($debug_data)
            ->build();
    }

    /**
     * Returns ExceptionHandlerHelper configration array with user configuration merged into built-in defaults.
     *
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\InvalidTypeException
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\NotIntegerException
     */
    protected static function getExceptionHandlerConfig(): array
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $default_config = [
            HttpException::class => [
                'handler' => HttpExceptionHandler::class,
                'pri'     => -100,
                'config'  => [
                    // used by unauthenticated() to obtain api and http code for the exception
                    HttpResponse::HTTP_UNAUTHORIZED         => [
                        RB::KEY_API_CODE => BaseApiCodes::EX_AUTHENTICATION_EXCEPTION(),
                    ],
                    // Required by ValidationException handler
                    HttpResponse::HTTP_UNPROCESSABLE_ENTITY => [
                        RB::KEY_API_CODE => BaseApiCodes::EX_VALIDATION_EXCEPTION(),
                    ],

                    RB::KEY_DEFAULT => [
                        RB::KEY_API_CODE  => BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
                        RB::KEY_HTTP_CODE => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                    ],
                ],
                // default config is built into handler.
            ],

            // default handler is mandatory. `default` entry MUST have both `api_code` and `http_code` set.
            RB::KEY_DEFAULT      => [
                'handler' => DefaultExceptionHandler::class,
                'pri'     => -127,
                'config'  => [
                    RB::KEY_API_CODE  => BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
                    RB::KEY_HTTP_CODE => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                ],
            ],
        ];

        /** @var array $user_handler_config */
        $user_handler_config = \Config::get(RB::CONF_KEY_EXCEPTION_HANDLER, []);
        $cfg = Util::mergeConfig($default_config, $user_handler_config );

        Util::sortArrayByPri($cfg);

        return $cfg;
    }

    /**
     * Returns config of exception handler class, configured to process specified exception class
     * or @null if no exception handler can be determined.
     *
     * @param \Throwable $ex Exception to handle
     *
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\InvalidTypeException
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\NotIntegerException
     */
    protected static function getHandlerConfig(\Throwable $ex): ?array
    {
        $result = null;

        $cls = \get_class($ex);
        $cfg = static::getExceptionHandlerConfig();

        // check for exact class name match...
        if (\array_key_exists($cls, $cfg)) {
            $result = $cfg[ $cls ];
        } else {
            // no exact match, then lets try with `instanceof`
            // Config entries are already sorted by priority.
            foreach (\array_keys($cfg) as $class_name) {
                /** @var string $class_name */
                if ($ex instanceof $class_name) {
                    $result = $cfg[ $class_name ];
                    break;
                }
            }
        }

        return $result;
    }

} // end of class
