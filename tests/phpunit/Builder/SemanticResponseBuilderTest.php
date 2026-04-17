<?php

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Builder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2026 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Ex\LogicException;
use MarcinOrlowski\ResponseBuilder\Exceptions\HttpCodeLockedException;
use MarcinOrlowski\ResponseBuilder\FailureResponseBuilder;
use MarcinOrlowski\ResponseBuilder\SuccessResponseBuilder;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Class SemanticResponseBuilderTest
 */
class SemanticResponseBuilderTest extends TestCase
{
	/**
	 * Checks if asAccepted() uses HTTP 202.
	 */
	public function testAsAccepted(): void
	{
		$this->response = SuccessResponseBuilder::asAccepted()->build();

		$this->assertEquals(HttpResponse::HTTP_ACCEPTED, $this->response->getStatusCode());

		$api = $this->getResponseSuccessObject(expected_http_code: HttpResponse::HTTP_ACCEPTED);
		
		$this->assertNull($api->getData());
		$msg_key = BaseApiCodes::getCodeMessageKey(BaseApiCodes::OK());
		/** @var string $msg_key */
		$this->assertEquals($this->langGet($msg_key), $api->getMessage());
		
	}

	/**
	 * Checks if asCreated() uses HTTP 201.
	 */
	public function testAsCreated(): void
	{
		$this->response = SuccessResponseBuilder::asCreated()->build();

		$this->assertEquals(HttpResponse::HTTP_CREATED, $this->response->getStatusCode());

		$api = $this->getResponseSuccessObject(expected_http_code: HttpResponse::HTTP_CREATED);
		$this->assertNull($api->getData());
		$msg_key = BaseApiCodes::getCodeMessageKey(BaseApiCodes::OK());
		/** @var string $msg_key */
		$this->assertEquals($this->langGet($msg_key), $api->getMessage());
	}

	/**
	 * Checks if asOk() uses HTTP 200.
	 */
	public function testAsOk(): void
	{
		$this->response = SuccessResponseBuilder::asOk()->build();

		$this->assertEquals(HttpResponse::HTTP_OK, $this->response->getStatusCode());

		$api = $this->getResponseSuccessObject(expected_http_code: HttpResponse::HTTP_OK);
		$this->assertNull($api->getData());
		$msg_key = BaseApiCodes::getCodeMessageKey(BaseApiCodes::OK());
		/** @var string $msg_key */
		$this->assertEquals($this->langGet($msg_key), $api->getMessage());
	}

	/**
	 * Checks if asForbidden() uses HTTP 403 and default API code.
	 */
	public function testAsForbidden(): void
	{
		$this->response = FailureResponseBuilder::asForbidden()->build();

		$this->assertEquals(HttpResponse::HTTP_FORBIDDEN, $this->response->getStatusCode());
	}

	/**
	 * Checks if asForbidden() uses custom API code if provided.
	 */
	public function testAsForbiddenWithCustomApiCode(): void
	{
		$api_code = BaseApiCodes::EX_HTTP_EXCEPTION();

		$this->response = FailureResponseBuilder::asForbidden($api_code)->build();

		$this->assertEquals(HttpResponse::HTTP_FORBIDDEN, $this->response->getStatusCode());

		$api = $this->getResponseErrorObject(BaseApiCodes::EX_HTTP_EXCEPTION(), HttpResponse::HTTP_FORBIDDEN);
		$this->assertNull($api->getData());
		$msg_key = BaseApiCodes::getCodeMessageKey($api_code);
		/** @var string $msg_key */
		$this->assertEquals($this->langGet($msg_key), $api->getMessage());
	}

	/**
	 * Checks if asInternalServerError() uses HTTP 500 and default API code.
	 */
	public function testAsInternalServerError(): void
	{
		$this->response = FailureResponseBuilder::asInternalServerError()->build();

		$this->assertEquals(HttpResponse::HTTP_INTERNAL_SERVER_ERROR, $this->response->getStatusCode());
	}

	/**
	 * Checks if asNotFound() uses HTTP 404 and default API code.
	 */
	public function testAsNotFound(): void
	{
		$this->response = FailureResponseBuilder::asNotFound()->build();

		$this->assertEquals(HttpResponse::HTTP_NOT_FOUND, $this->response->getStatusCode());
	}

	/**
	 * Checks if asTooManyRequests() uses HTTP 429 and default API code.
	 */
	public function testAsTooManyRequests(): void
	{
		$this->response = FailureResponseBuilder::asTooManyRequests()->build();

		$this->assertEquals(HttpResponse::HTTP_TOO_MANY_REQUESTS, $this->response->getStatusCode());
	}

	/**
	 * Checks if asUnauthorized() uses HTTP 401 and default API code.
	 */
	public function testAsUnauthorized(): void
	{
		$this->response = FailureResponseBuilder::asUnauthorized()->build();

		$this->assertEquals(HttpResponse::HTTP_UNAUTHORIZED, $this->response->getStatusCode());
	}

	/**
	 * Checks if asUnprocessableEntity() uses HTTP 422 and default API code.
	 */
	public function testAsUnprocessableEntity(): void
	{
		$this->response = FailureResponseBuilder::asUnprocessableEntity()->build();

		$this->assertEquals(HttpResponse::HTTP_UNPROCESSABLE_ENTITY, $this->response->getStatusCode());
	}

	/**
	 * Checks if preset HTTP code cannot be overridden.
	 */
	public function testPresetHttpCodeCannotBeOverridden(): void
	{
		$this->expectException(HttpCodeLockedException::class);

		SuccessResponseBuilder::asOk()
			->withHttpCode(HttpResponse::HTTP_CREATED)
			->build();
	}
} // end of class