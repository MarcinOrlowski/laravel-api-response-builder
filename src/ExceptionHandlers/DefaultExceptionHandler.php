<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\ExceptionHandlers;

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

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Contracts\ExceptionHandlerContract;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class DefaultExceptionHandler implements ExceptionHandlerContract
{
	public function handle(array $user_config, \Exception $ex): ?array
	{
		$defaults = [
			ResponseBuilder::KEY_API_CODE  => BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
			ResponseBuilder::KEY_HTTP_CODE => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
		];

		return \array_replace($defaults, $user_config);
	}
}
