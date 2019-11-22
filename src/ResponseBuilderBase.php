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

use Symfony\Component\HttpFoundation\Response as HttpResponse;


abstract class ResponseBuilderBase
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
    public const CONF_CONFIG                     = 'response_builder';
    public const CONF_KEY_DEBUG_DEBUG_KEY        = self::CONF_CONFIG . '.debug.debug_key';
    public const CONF_KEY_DEBUG_EX_TRACE_ENABLED = self::CONF_CONFIG . '.debug.exception_handler.trace_enabled';
    public const CONF_KEY_DEBUG_EX_TRACE_KEY     = self::CONF_CONFIG . '.debug.exception_handler.trace_key';
    public const CONF_KEY_MAP                    = self::CONF_CONFIG . '.map';
    public const CONF_KEY_ENCODING_OPTIONS       = self::CONF_CONFIG . '.encoding_options';
    public const CONF_KEY_CONVERTER              = self::CONF_CONFIG . '.converter';
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
    public const KEY_PRI     = 'pri';
    public const KEY_HANDLER = 'handler';
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
}
