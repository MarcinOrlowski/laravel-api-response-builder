<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Contracts;

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
interface ExceptionHandlerContract
{
	/**
	 * Handles given exception. If for any reason exception cannot be handled, MUST return @null,
	 * otherwise it returns array with the following keys. Keys marked "mandatory" MUST be present
	 * in returned array:
	 *
	 *   `api_code`   : (int) mandatory api_code to be used for given exception
	 *   `http_code`  : (int) optional HTTP code. If not specified, exception's HTTP status code will be used.
	 *   `msg_key`    : (string) optional localization string key (ie. 'app.my_error_string') which will be used
	 *                  if exception's message is empty. If `msg_key` is not provided, ExceptionHandler will
	 *                  fall back to built-in message, with message key built as "http_XXX" where XXX is
	 *                  HTTP code used to handle given the exception.
	 *   `msg_enforce`: (boolean) optional. if `true`, then fallback message (either one specified with `msg_key`,
	 *                  or built-in one will **always** be used, ignoring exception's message string completely.
	 *                  If set to `false` (default) then it will enforce either built-in message (if no
	 *                  `msg_key` is set, or message referenced by `msg_key` completely ignoring exception
	 *                  message ($ex->getMessage()).
	 *
	 * @param array      $config Config array (can be empty) with any keys required by given handle.
	 * @param \Exception $ex     The exception to handle.
	 *
	 * @return array|null
	 */
	public function handle(array $config, \Exception $ex): ?array;
}
