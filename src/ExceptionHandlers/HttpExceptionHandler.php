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

final class HttpExceptionHandler implements ExceptionHandlerContract
{
	public function handle(array $user_config, \Exception $ex): ?array
	{
		$default_config = [
			// used by unauthenticated() to obtain api and http code for the exception
			HttpResponse::HTTP_UNAUTHORIZED         => [
				'api_code' => /** @scrutinizer ignore-deprecated */ BaseApiCodes::EX_AUTHENTICATION_EXCEPTION(),
			],
			// Required by ValidationException handler
			HttpResponse::HTTP_UNPROCESSABLE_ENTITY => [
				'api_code' => /** @scrutinizer ignore-deprecated */ BaseApiCodes::EX_VALIDATION_EXCEPTION(),
			],

			// Default entry MUST exists. Enforced by unit tests.
			ResponseBuilder::KEY_DEFAULT            => [
				'api_code' => BaseApiCodes::EX_HTTP_EXCEPTION(),
			],
		];

		$config = \array_replace($default_config, $user_config);

		$http_code = $ex->getStatusCode();
		$result = $config[ $http_code ] ?? null;

		if ($result === null) {
			$result = $config[ ResponseBuilder::KEY_DEFAULT ];
		}

		if (!\array_key_exists(ResponseBuilder::KEY_HTTP_CODE, $result)) {
			$result[ ResponseBuilder::KEY_HTTP_CODE ] = $http_code;
		}
		if (\array_key_exists(ResponseBuilder::KEY_MSG_KEY, $result)) {
			$result[ ResponseBuilder::KEY_MSG_KEY ] = \sprintf('response-builder::builder.http_%d', $http_code);
		}
		return $result;
	}
}
