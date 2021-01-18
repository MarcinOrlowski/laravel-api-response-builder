<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\ExceptionHandlers;

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

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Contracts\ExceptionHandlerContract;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Default exception handler. Kicks in if there's no better handler found.
 */
final class DefaultExceptionHandler implements ExceptionHandlerContract
{
	public function handle(array $user_config, /** @scrutinizer ignore-unused */ \Exception $ex): ?array
	{
		$defaults = [
			RB::KEY_API_CODE  => BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
			RB::KEY_HTTP_CODE => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
		];

		return \array_replace($defaults, $user_config);
	}
}
