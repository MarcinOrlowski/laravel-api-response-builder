<?php

use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use MarcinOrlowski\ResponseBuilder\ErrorCode;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class SuccessTest extends ResponseBuilderTestCase
{

	/**
	 * Check success()
	 */
	public function testSuccess()
	{
		$this->response = ResponseBuilder::success();
		$j = $this->getResponseSuccessObject();

		$this->assertNull($j->data);
	}

	public function testSuccess_WithDataAndHttpCode()
	{
		$payloads = [
			null,
			[$this->getRandomString() => $this->getRandomString()],
		];
		$http_codes = [HttpResponse::HTTP_OK       => null,
		               HttpResponse::HTTP_ACCEPTED => HttpResponse::HTTP_ACCEPTED,
		               HttpResponse::HTTP_OK       => HttpResponse::HTTP_OK];

		foreach($payloads as $payload) {
			foreach($http_codes as $http_code_expect => $http_code_send) {
				$this->response = ResponseBuilder::success($payload, $http_code_send);

				$j = $this->getResponseSuccessObject(ErrorCode::OK, $http_code_expect);

				if ($payload !== null) {
					$payload = (object)$payload;
				}
				$this->assertEquals($payload, $j->data);
			}
		}
	}

	public function testSuccessWithHttpCode()
	{
		$http_codes = [HttpResponse::HTTP_ACCEPTED,
		               HttpResponse::HTTP_OK];
		foreach($http_codes as $http_code) {
			$this->response = ResponseBuilder::successWithHttpCode($http_code);
			$j = $this->getResponseSuccessObject(0, $http_code);
			$this->assertNull($j->data);
		}
	}


	//----[ success ]-------------------------------------------

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessErrorCodeMustBeInt() {
		ResponseBuilder::success(null, 'foo');
	}

	public function testSuccessWithHttpCodeFallback() {
		$this->response = ResponseBuilder::successWithHttpCode(null);
		$this->assertResponseOk();

		$j = json_decode($this->response->getContent());
		$this->assertTrue(is_object($j));
		$this->validateSuccessCommon($j);
		$this->assertNull($j->data);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessWithInvalidHttpCode() {
		ResponseBuilder::successWithHttpCode('invalid');
	}
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessWithTooBigHttpCode() {
		ResponseBuilder::successWithHttpCode(666);
	}
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSuccessWithTooLowHttpCode() {
		ResponseBuilder::successWithHttpCode(0);
	}

	public function testBuildSuccessResponse_InvalidReturnCode() {
		$this->expectException(\InvalidArgumentException::class);

		$obj = new ResponseBuilder();
		$method = $this->getProtectedMethod(get_class($obj), 'buildSuccessResponse');
		$method->invokeArgs($obj, [null, 'string-is-invalid-code']);
	}
}