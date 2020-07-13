<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\ExceptionHandlers;

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

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
final class ValidationExceptionHandler extends BaseExceptionHandler
{
	public function handle(array $user_config, \Exception $ex): ?array
	{
		return $this->finalize(BaseApiCodes::EX_VALIDATION_EXCEPTION(), HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
	}
}
