<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Contracts;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2020 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */
interface ExceptionHandlerContract
{
	/**
	 * Handles given exception. If for any reason exception cannot be handled, MUST return @null,
	 * otherwise it returns array with the following keys. ALL KEYS MUST BE PRESENT:
	 *
	 *   api_code
	 *   http_code
	 *
	 *
	 * @param array      $config Config array (can be empty) with any keys required by given handle.
	 * @param \Exception $ex     The exception to handle.
	 *
	 * @return array|null
	 */
	public function handle(array $config, \Exception $ex): ?array;
}
