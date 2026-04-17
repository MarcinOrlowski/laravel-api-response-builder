<?php

declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Builds semantically named error responses with preset HTTP status codes.
 */
class FailureResponseBuilder extends ResponseBuilder
{
	/**
	 * Returns an error response builder with HTTP 403 Forbidden status code.
	 *
	 * If no API code is provided, authentication exception API code is used.
	 *
	 * @param int|null $api_code API code to be returned or @null to use
	 *                           BaseApiCodes::EX_AUTHENTICATION_EXCEPTION().
	 *
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\NotIntegerException
	 * @throws Ex\InvalidTypeException
	 */
	public static function asForbidden(?int $api_code = null): self
	{
		return static::asError($api_code ?? BaseApiCodes::EX_AUTHENTICATION_EXCEPTION())
			->withPresetHttpCode(HttpResponse::HTTP_FORBIDDEN);
	}

	/**
	 * Returns an error response builder with HTTP 500 Internal Server Error status code.
	 *
	 * If no API code is provided, generic HTTP exception API code is used.
	 *
	 * @param int|null $api_code API code to be returned or @null to use
	 *                           BaseApiCodes::EX_HTTP_EXCEPTION().
	 *
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\NotIntegerException
	 * @throws Ex\InvalidTypeException
	 */
	public static function asInternalServerError(?int $api_code = null): self
	{
		return static::asError($api_code ?? BaseApiCodes::EX_HTTP_EXCEPTION())
			->withPresetHttpCode(HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * Returns an error response builder with HTTP 404 Not Found status code.
	 *
	 * If no API code is provided, not found exception API code is used.
	 *
	 * @param int|null $api_code API code to be returned or @null to use
	 *                           BaseApiCodes::EX_HTTP_NOT_FOUND().
	 *
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\NotIntegerException
	 * @throws Ex\InvalidTypeException
	 */
	public static function asNotFound(?int $api_code = null): self
	{
		return static::asError($api_code ?? BaseApiCodes::EX_HTTP_NOT_FOUND())
			->withPresetHttpCode(HttpResponse::HTTP_NOT_FOUND);
	}

	/**
	 * Returns an error response builder with HTTP 429 Too Many Requests status code.
	 *
	 * If no API code is provided, service unavailable exception API code is used.
	 *
	 * @param int|null $api_code API code to be returned or @null to use
	 *                           BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE().
	 *
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\NotIntegerException
	 * @throws Ex\InvalidTypeException
	 */
	public static function asTooManyRequests(?int $api_code = null): self
	{
		return static::asError($api_code ?? BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE())
			->withPresetHttpCode(HttpResponse::HTTP_TOO_MANY_REQUESTS);
	}

	/**
	 * Returns an error response builder with HTTP 401 Unauthorized status code.
	 *
	 * If no API code is provided, authentication exception API code is used.
	 *
	 * @param int|null $api_code API code to be returned or @null to use
	 *                           BaseApiCodes::EX_AUTHENTICATION_EXCEPTION().
	 *
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\NotIntegerException
	 * @throws Ex\InvalidTypeException
	 */
	public static function asUnauthorized(?int $api_code = null): self
	{
		return static::asError($api_code ?? BaseApiCodes::EX_AUTHENTICATION_EXCEPTION())
			->withPresetHttpCode(HttpResponse::HTTP_UNAUTHORIZED);
	}

	/**
	 * Returns an error response builder with HTTP 422 Unprocessable Entity status code.
	 *
	 * If no API code is provided, validation exception API code is used.
	 *
	 * @param int|null $api_code API code to be returned or @null to use
	 *                           BaseApiCodes::EX_VALIDATION_EXCEPTION().
	 *
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\NotIntegerException
	 * @throws Ex\InvalidTypeException
	 */
	public static function asUnprocessableEntity(?int $api_code = null): self
	{
		return static::asError($api_code ?? BaseApiCodes::EX_VALIDATION_EXCEPTION())
			->withPresetHttpCode(HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
	}
}
