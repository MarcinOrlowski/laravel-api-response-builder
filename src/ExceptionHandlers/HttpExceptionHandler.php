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
 * Handles HttpException
 */
final class HttpExceptionHandler implements ExceptionHandlerContract
{
	public function handle(array $user_config, \Exception $ex): ?array
	{
		$default_config = [
			// used by unauthenticated() to obtain api and http code for the exception
			HttpResponse::HTTP_UNAUTHORIZED         => [
				'api_code' => BaseApiCodes::EX_AUTHENTICATION_EXCEPTION(),
			],
			// Required by ValidationException handler
			HttpResponse::HTTP_UNPROCESSABLE_ENTITY => [
				'api_code' => BaseApiCodes::EX_VALIDATION_EXCEPTION(),
			],

			// Default entry MUST exists. Enforced by unit tests.
			RB::KEY_DEFAULT            => [
				'api_code' => BaseApiCodes::EX_HTTP_EXCEPTION(),
			],
		];

		$config = \array_replace($default_config, $user_config);

		$http_code = $ex->getStatusCode();
		$result = $config[ $http_code ] ?? null;

		// If we do not have dedicated entry fort this particular http_code,
		// fall back to default value.
		if ($result === null) {
			$result = $config[ RB::KEY_DEFAULT ];
		}

		// Some defaults to fall back to if not set in user config.
		$fallback = [
			RB::KEY_HTTP_CODE => $http_code,
			RB::KEY_MSG_KEY   => \sprintf('response-builder::builder.http_%d', $http_code),
		];
		return \array_replace($fallback, $result);
	}
}
