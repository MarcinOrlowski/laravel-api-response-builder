<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

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

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;


/**
 * Builds standardized HttpResponse response object
 */
class ResponseBuilder extends ResponseBuilderBase
{
	/** @var bool */
	protected $success = false;

	/** @var int */
	protected $api_code;

	/** @var int|null */
	protected $http_code = null;

	/** @var mixed */
	protected $data = null;

	/** @var string */
	protected $message = null;

	/** @var array */
	protected $placeholders = [];

	/** @var int|null */
	protected $json_opts = null;

	/** @var array */
	protected $debug_data = [];

	/** @var array */
	protected $http_headers = [];

	// -----------------------------------------------------------------------------------------------------------

	/**
	 * Private constructor. Use asSuccess() and asError() static methods to obtain instance of Builder.
	 *
	 * @param bool $success
	 * @param int  $api_code
	 */
	protected function __construct(bool $success, int $api_code)
	{
		$this->success = $success;
		$this->api_code = $api_code;
	}

	// -----------------------------------------------------------------------------------------------------------

	/**
	 * Returns success
	 *
	 * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
	 *                                         of the JSON response, single supported object or @null if there's no
	 *                                         to be returned.
	 * @param integer|null      $api_code      API code to be returned or @null to use value of BaseApiCodes::OK().
	 * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
	 *                                         substitution or @null if none.
	 * @param integer|null      $http_code     HTTP code to be used for HttpResponse sent or @null
	 *                                         for default DEFAULT_HTTP_CODE_OK.
	 * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
	 *                                         options or pass @null to use value from your config (or defaults).
	 *
	 * @return HttpResponse
	 */
	public static function success($data = null, $api_code = null, array $placeholders = null,
	                               int $http_code = null, int $json_opts = null): HttpResponse
	{
		return static::asSuccess($api_code)
			->withData($data)
			->withPlaceholders($placeholders)
			->withHttpCode($http_code)
			->withJsonOptions($json_opts)
			->build();
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error
	 * message uses placeholders as well as return data payload
	 *
	 * @param integer           $api_code      Your API code to be returned with the response object.
	 * @param array|null        $placeholders  Placeholders passed to Lang::get() for message placeholders
	 *                                         substitution or @null if none.
	 * @param object|array|null $data          Array of primitives and supported objects to be returned in 'data' node
	 *                                         of the JSON response, single supported object or @null if there's no
	 *                                         to be returned.
	 * @param integer|null      $http_code     HTTP code to be used for HttpResponse sent or @null
	 *                                         for default DEFAULT_HTTP_CODE_ERROR.
	 * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php for supported
	 *                                         options or pass @null to use value from your config (or defaults).
	 *
	 * @return HttpResponse
	 */
	public static function error(int $api_code, array $placeholders = null, $data = null, int $http_code = null,
	                             int $json_opts = null): HttpResponse
	{
		return static::asError($api_code)
			->withPlaceholders($placeholders)
			->withData($data)
			->withHttpCode($http_code)
			->withJsonOptions($json_opts)
			->build();
	}

	// -----------------------------------------------------------------------------------------------------------

	/**
	 * @param int|null $api_code
	 *
	 * @return \MarcinOrlowski\ResponseBuilder\ResponseBuilder
	 */
	public static function asSuccess(int $api_code = null): self
	{
		return new static(true, $api_code ?? BaseApiCodes::OK());
	}

	/**
	 * @param int $api_code
	 *
	 * @return \MarcinOrlowski\ResponseBuilder\ResponseBuilder
	 */
	public static function asError(int $api_code): self
	{
		$code_ok = BaseApiCodes::OK();
		if ($api_code !== $code_ok) {
			Validator::assertIsIntRange('api_code', $api_code, BaseApiCodes::getMinCode(), BaseApiCodes::getMaxCode());
		}
		if ($api_code === $code_ok) {
			throw new \OutOfBoundsException(
				"Error response cannot use api_code of value {$code_ok} which is reserved for OK.");
		}

		return new static(false, $api_code);
	}

	/**
	 * @param int|null $http_code
	 *
	 * @return $this
	 */
	public function withHttpCode(int $http_code = null): self
	{
		Validator::assertIsType('http_code', $http_code, [
			Type::INTEGER,
			Type::NULL]);
		$this->http_code = $http_code;

		return $this;
	}

	/**
	 * @param null $data
	 *
	 * @return $this
	 */
	public function withData($data = null): self
	{
		Validator::assertIsType('data', $data, [
			Type::ARRAY,
			Type::BOOLEAN,
			Type::INTEGER,
			Type::NULL,
			Type::OBJECT,
			Type::STRING,
		]);
		$this->data = $data;

		return $this;
	}

	/**
	 * @param int|null $json_opts
	 *
	 * @return $this
	 */
	public function withJsonOptions(int $json_opts = null): self
	{
		Validator::assertIsType('json_opts', $json_opts, [Type::INTEGER,
		                                                  Type::NULL]);
		$this->json_opts = $json_opts;

		return $this;
	}

	/**
	 * @param array|null $debug_data
	 *
	 * @return $this
	 */
	public function withDebugData(array $debug_data = null): self
	{
		Validator::assertIsType('$debug_data', $debug_data, [Type::ARRAY,
		                                                     Type::NULL]);
		$this->debug_data = $debug_data;

		return $this;
	}

	/**
	 * @param string|null $msg
	 *
	 * @return $this
	 */
	public function withMessage(string $msg = null): self
	{
		Validator::assertIsType('message', $msg, [Type::STRING,
		                                          Type::NULL]);
		$this->message = $msg;

		return $this;
	}

	/**
	 * @param array|null $placeholders
	 *
	 * @return $this
	 */
	public function withPlaceholders(array $placeholders = null): self
	{
		$this->placeholders = $placeholders;

		return $this;
	}

	/**
	 * @param array|null $http_headers
	 *
	 * @return $this
	 */
	public function withHttpHeaders(array $http_headers = null): self
	{
		$this->http_headers = $http_headers ?? [];

		return $this;
	}

	/**
	 * Builds and returns final HttpResponse. It's safe to call build() as many times as needed, as no
	 * internal state is changed. It's also safe to alter any parameter set previously and call build()
	 * again to get new response object that includes new changes.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function build(): HttpResponse
	{
		$api_code = $this->api_code;
		Validator::assertIsInt('api_code', $api_code);

		$msg_or_api_code = $this->message ?? $api_code;
		$http_headers = $this->http_headers ?? [];

		if ($this->success) {
			$api_code = $api_code ?? BaseApiCodes::OK();
			$http_code = $this->http_code ?? RB::DEFAULT_HTTP_CODE_OK;

			Validator::assertOkHttpCode($http_code);

			$result = $this->make($this->success, $api_code, $msg_or_api_code, $this->data, $http_code,
				$this->placeholders, $http_headers, $this->json_opts);
		} else {
			$http_code = $this->http_code ?? RB::DEFAULT_HTTP_CODE_ERROR;

			Validator::assertErrorHttpCode($http_code);

			$result = $this->make(false, $api_code, $msg_or_api_code, $this->data, $http_code, $this->placeholders,
				$this->http_headers, $this->json_opts, $this->debug_data);
		}

		return $result;
	}

	/**
	 * @param boolean           $success         @true if response reports successful operation, @false otherwise.
	 * @param integer           $api_code        Your API code to be returned with the response object.
	 * @param string|integer    $msg_or_api_code message string or valid API code to get message for
	 * @param object|array|null $data            optional additional data to be included in response object
	 * @param integer|null      $http_code       HTTP code for the HttpResponse or @null for either DEFAULT_HTTP_CODE_OK
	 *                                           or DEFAULT_HTTP_CODE_ERROR depending on the $success.
	 * @param array|null        $placeholders    Placeholders passed to Lang::get() for message placeholders
	 *                                           substitution or @null if none.
	 * @param array|null        $http_headers    Optional HTTP headers to be returned in the response.
	 * @param integer|null      $json_opts       See http://php.net/manual/en/function.json-encode.php for supported
	 *                                           options or pass @null to use value from your config (or defaults).
	 * @param array|null        $debug_data      Optional debug data array to be added to returned JSON.
	 *
	 * @return HttpResponse
	 *
	 * @throws \InvalidArgumentException If $api_code is neither a string nor valid integer code.
	 * @throws \InvalidArgumentException if $data is an object of class that is not configured in "classes" mapping.
	 *
	 * @noinspection PhpTooManyParametersInspection
	 */
	protected function make(bool $success, int $api_code, $msg_or_api_code, $data = null,
	                        int $http_code = null, array $placeholders = null, array $http_headers = null,
	                        int $json_opts = null, array $debug_data = null): HttpResponse
	{
		$http_headers = $http_headers ?? [];
		$http_code = $http_code ?? ($success ? RB::DEFAULT_HTTP_CODE_OK : RB::DEFAULT_HTTP_CODE_ERROR);
		$json_opts = $json_opts ?? Config::get(RB::CONF_KEY_ENCODING_OPTIONS, RB::DEFAULT_ENCODING_OPTIONS);

		Validator::assertIsInt('encoding_options', $json_opts);

		Validator::assertIsInt('api_code', $api_code);
		if (!BaseApiCodes::isCodeValid($api_code)) {
			Validator::assertIsIntRange('api_code', $api_code, BaseApiCodes::getMinCode(), BaseApiCodes::getMaxCode());
		}

		return Response::json(
			$this->buildResponse($success, $api_code, $msg_or_api_code, $placeholders, $data, $debug_data),
			$http_code, $http_headers, $json_opts);
	}

	/**
	 * Creates standardised API response array. This is final method called in the whole pipeline before we
	 * return final JSON back to client. If you want to manipulate your response, this is the place to do that.
	 * If you set APP_DEBUG to true, 'code_hex' field will be additionally added to reported JSON for easier
	 * manual debugging.
	 *
	 * @param boolean           $success         @true if response reports successful operation, @false otherwise.
	 * @param integer           $api_code        Your API code to be returned with the response object.
	 * @param string|integer    $msg_or_api_code Message string or valid API code to get message for.
	 * @param array|null        $placeholders    Placeholders passed to Lang::get() for message placeholders
	 *                                           substitution or @null if none.
	 * @param object|array|null $data            API response data if any
	 * @param array|null        $debug_data      optional debug data array to be added to returned JSON.
	 *
	 * @return array response ready to be encoded as json and sent back to client
	 *
	 * @throws \RuntimeException in case of missing or invalid "classes" mapping configuration
	 *
	 * @noinspection PhpTooManyParametersInspection
	 */
	protected function buildResponse(bool $success, int $api_code,
	                                 $msg_or_api_code, array $placeholders = null,
	                                 $data = null, array $debug_data = null): array
	{
		// ensure $data is either @null, array or object of class with configured mapping.
		$data = (new Converter())->convert($data);
//		if ($data !== null && !\is_object($data)) {
//			// ensure we get object in final JSON structure in data node
//			$data = (object)$data;
//		}

		// get human readable message for API code or use message string (if given instead of API code)
		if (\is_int($msg_or_api_code)) {
			$message = $this->getMessageForApiCode($success, $msg_or_api_code, $placeholders);
		} else {
			Validator::assertIsString('message', $msg_or_api_code);
			$message = $msg_or_api_code;
		}

		/** @noinspection PhpUndefinedClassInspection */
		$response = [
			RB::KEY_SUCCESS => $success,
			RB::KEY_CODE    => $api_code,
			RB::KEY_LOCALE  => \App::getLocale(),
			RB::KEY_MESSAGE => $message,
			RB::KEY_DATA    => $data,
		];

		if ($debug_data !== null) {
			$debug_key = Config::get(RB::CONF_KEY_DEBUG_DEBUG_KEY, RB::KEY_DEBUG);
			$response[ $debug_key ] = $debug_data;
		}

		return $response;
	}

	/**
	 * If $msg_or_api_code is integer value, returns human readable message associated with that code (with
	 * fallback to built-in default string if no api code mapping is set. If $msg_or_api_code is a string,
	 * returns it unaltered.
	 *
	 * @param boolean    $success      @true if response reports successful operation, @false otherwise.
	 * @param integer    $api_code     Your API code to be returned with the response object.
	 * @param array|null $placeholders Placeholders passed to Lang::get() for message placeholders
	 *                                 substitution or @null if none.
	 *
	 * @return string
	 */
	protected function getMessageForApiCode(bool $success, int $api_code, array $placeholders = null): string
	{
		// We got integer value here not a message string, so we need to check if we have the mapping for
		// this string already configured.
		$key = BaseApiCodes::getCodeMessageKey($api_code);
		if ($key === null) {
			// nope, let's get the default one instead, based of
			$fallback_code = $success ? BaseApiCodes::OK() : BaseApiCodes::NO_ERROR_MESSAGE();
			$key = BaseApiCodes::getCodeMessageKey($fallback_code);
		}

		$placeholders = $placeholders ?? [];
		if (!\array_key_exists('api_code', $placeholders)) {
			$placeholders['api_code'] = $api_code;
		}

		return \Lang::get($key, $placeholders);
	}
}
