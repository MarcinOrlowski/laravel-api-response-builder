<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2021 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Contains commonly used constants
 *
 * @package MarcinOrlowski\ResponseBuilder
 */
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

	/** @var string */
	public const CONF_CONFIG                            = 'response_builder';
	/** @var string */
	public const CONF_KEY_DEBUG_DEBUG_KEY               = self::CONF_CONFIG . '.debug.debug_key';
	/** @var string */
	public const CONF_KEY_DEBUG_EX_TRACE_ENABLED        = self::CONF_CONFIG . '.debug.exception_handler.trace_enabled';
	/** @var string */
	public const CONF_KEY_DEBUG_EX_TRACE_KEY            = self::CONF_CONFIG . '.debug.exception_handler.trace_key';
	/** @var string */
	public const CONF_KEY_DEBUG_CONVERTER_DEBUG_ENABLED = self::CONF_CONFIG . '.debug.converter.debug_enabled';
	/** @var string */
	public const CONF_KEY_MAP                           = self::CONF_CONFIG . '.map';
	/** @var string */
	public const CONF_KEY_ENCODING_OPTIONS              = self::CONF_CONFIG . '.encoding_options';
	/** @var string */
	public const CONF_KEY_CONVERTER                     = self::CONF_CONFIG . '.converter';
	/** @var string */
	public const CONF_KEY_CONVERTER_CLASSES             = self::CONF_KEY_CONVERTER . '.classes';
	/** @var string */
	public const CONF_KEY_CONVERTER_PRIMITIVES          = self::CONF_KEY_CONVERTER . '.primitives';
	/** @var string */
	public const CONF_KEY_MIN_CODE                      = self::CONF_CONFIG . '.min_code';
	/** @var string */
	public const CONF_KEY_MAX_CODE                      = self::CONF_CONFIG . '.max_code';
	/** @var string */
	public const CONF_KEY_EXCEPTION_HANDLER             = self::CONF_CONFIG . '.exception_handler';
	/** @var string */
	public const CONF_KEY_DATA_ALWAYS_OBJECT            = self::CONF_CONFIG . '.data_always_object';

	/**
	 * Default keys to be used by exception handler while adding debug information
	 */
	/** @var string */
	public const KEY_DEBUG     = 'debug';
	/** @var string */
	public const KEY_TRACE     = 'trace';
	/** @var string */
	public const KEY_CLASS     = 'class';
	/** @var string */
	public const KEY_FILE      = 'file';
	/** @var string */
	public const KEY_LINE      = 'line';
	/** @var string */
	public const KEY_KEY       = 'key';
	/** @var string */
	public const KEY_PRI       = 'pri';
	/** @var string */
	public const KEY_HANDLER   = 'handler';
	/** @var string */
	public const KEY_SUCCESS   = 'success';
	/** @var string */
	public const KEY_CODE      = 'code';
	/** @var string */
	public const KEY_LOCALE    = 'locale';
	/** @var string */
	public const KEY_MESSAGE   = 'message';
	/** @var string */
	public const KEY_DATA      = 'data';
	/** @var string */
	public const KEY_CONFIG    = 'config';
	/** @var string */
	public const KEY_DEFAULT   = 'default';
	/** @var string */
	public const KEY_API_CODE  = 'api_code';
	/** @var string */
	public const KEY_HTTP_CODE = 'http_code';
	/** @var string */
	public const KEY_MSG_KEY   = 'msg_key';
	/** @var string */
	public const KEY_MSG_FORCE = 'msg_force';

	/**
	 * Default key to be used by exception handler while processing ValidationException
	 * to return all the error messages
	 *
	 * @var string
	 */
	/** @var string */
	public const KEY_MESSAGES = 'messages';

	/**
	 * Default JSON encoding options.
	 *
	 * @var int
	 */
	public const DEFAULT_ENCODING_OPTIONS = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
}
