<?php

declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Builds semantically named success responses with preset HTTP status codes.
 */
class SuccessResponseBuilder extends ResponseBuilder
{
	/**
	 * Returns a success response builder with HTTP 202 Accepted status code.
	 *
	 * @param int|null $api_code API code to be returned or @null to use the default success API code.
	 *
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\NotIntegerException
	 * @throws Ex\InvalidTypeException
	 */
	public static function asAccepted(?int $api_code = null): self
	{
		return static::asSuccess($api_code)
			->withPresetHttpCode(HttpResponse::HTTP_ACCEPTED);
	}

	/**
	 * Returns a success response builder with HTTP 201 Created status code.
	 *
	 * @param int|null $api_code API code to be returned or @null to use the default success API code.
	 *
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\NotIntegerException
	 * @throws Ex\InvalidTypeException
	 */
	public static function asCreated(?int $api_code = null): self
	{
		return static::asSuccess($api_code)
			->withPresetHttpCode(HttpResponse::HTTP_CREATED);
	}

	/**
	 * Returns a success response builder with HTTP 200 OK status code.
	 *
	 * @param int|null $api_code API code to be returned or @null to use the default success API code.
	 *
	 * @throws Ex\MissingConfigurationKeyException
	 * @throws Ex\NotIntegerException
	 * @throws Ex\InvalidTypeException
	 */
	public static function asOk(?int $api_code = null): self
	{
		return static::asSuccess($api_code)
			->withPresetHttpCode(HttpResponse::HTTP_OK);
	}
}
