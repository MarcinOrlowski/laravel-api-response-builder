<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;

/**
 * Builds standardized HttpResponse response object
 */
class ResponseBuilder extends ResponseBuilderBase
{
    protected bool    $success      = false;
    protected int     $api_code;
    protected ?int    $http_code    = null;
    protected ?string $message      = null;
    protected ?array  $placeholders = null;
    protected ?int    $json_opts    = null;
    protected ?array  $debug_data   = null;
    protected array   $http_headers = [];

    /** @var mixed|null $data */
    protected $data = null;

    // ---------------------------------------------------------------------------------------------

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

    // ---------------------------------------------------------------------------------------------

    /**
     * Returns success
     *
     * @param mixed|null   $data         Array of primitives and supported objects to be returned in
     *                                   'data' node of the JSON response, single supported object
     *                                   or @null if there's no to be returned.
     * @param integer|null $api_code     API code to be returned or @null to use value of
     *                                   BaseApiCodes::OK().
     * @param array|null   $placeholders Placeholders passed to Lang::get() for message placeholders
     *                                   substitution or @null if none.
     * @param integer|null $http_code    HTTP code to be used for HttpResponse sent or @null
     *                                   for default DEFAULT_HTTP_CODE_OK.
     * @param integer|null $json_opts    See http://php.net/manual/en/function.json-encode.php for
     *                                   supported options or pass @null to use value from your
     *                                   config (or defaults).
     *
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\ConfigurationNotFoundException
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\ArrayWithMixedKeysException
     * @throws Ex\InvalidTypeException
     * @throws Ex\NotIntegerException
     */
    public static function success($data = null, int $api_code = null, array $placeholders = null,
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
     * @param array|null        $placeholders  Placeholders passed to Lang::get() for message
     *                                         placeholders substitution or @null if none.
     * @param object|array|null $data          Array of primitives and supported objects to be
     *                                         returned in 'data' node of the JSON response, single
     *                                         supported object or @null if there's no to be returned.
     * @param integer|null      $http_code     HTTP code to be used for HttpResponse sent or @null
     *                                         for default DEFAULT_HTTP_CODE_ERROR.
     * @param integer|null      $json_opts     See http://php.net/manual/en/function.json-encode.php
     *                                         for supported options or pass @null to use value from
     *                                         your config (or defaults).
     *
     * @throws Ex\ArrayWithMixedKeysException
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\ConfigurationNotFoundException
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\InvalidTypeException
     * @throws Ex\NotIntegerException
     */
    public static function error(int $api_code, array $placeholders = null, $data = null,
                                 int $http_code = null,
                                 int $json_opts = null): HttpResponse
    {
        return static::asError($api_code)
            ->withPlaceholders($placeholders)
            ->withData($data)
            ->withHttpCode($http_code)
            ->withJsonOptions($json_opts)
            ->build();
    }

    // ---------------------------------------------------------------------------------------------

    /**
     * @param int|null $api_code
     *
     * @throws Ex\InvalidTypeException
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\NotIntegerException
     */
    public static function asSuccess(int $api_code = null): self
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new static(true, $api_code ?? BaseApiCodes::OK());
    }

    /**
     * @param int $api_code
     *
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\NotIntegerException
     * @throws Ex\InvalidTypeException
     */
    public static function asError(int $api_code): self
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $code_ok = BaseApiCodes::OK();
        if ($api_code !== $code_ok) {
            /** @noinspection PhpUnhandledExceptionInspection */
            Validator::assertIsIntRange('api_code', $api_code,
                BaseApiCodes::getMinCode(), BaseApiCodes::getMaxCode());
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
     * @throws Ex\InvalidTypeException
     */
    public function withHttpCode(int $http_code = null): self
    {
        Validator::assertIsType('http_code', $http_code, [
            Type::INTEGER,
            Type::NULL,
        ]);
        $this->http_code = $http_code;

        return $this;
    }

    /**
     * @param mixed|null $data
     *
     * @throws Ex\InvalidTypeException
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
            Type::DOUBLE,
        ]);
        $this->data = $data;

        return $this;
    }

    /**
     * @param int|null $json_opts
     *
     * @throws Ex\InvalidTypeException
     */
    public function withJsonOptions(int $json_opts = null): self
    {
        Validator::assertIsType('json_opts', $json_opts, [Type::INTEGER,
                                                          Type::NULL,
        ]);
        $this->json_opts = $json_opts;

        return $this;
    }

    /**
     * @param array|null $debug_data
     *
     * @throws Ex\InvalidTypeException
     */
    public function withDebugData(array $debug_data = null): self
    {
        Validator::assertIsType('$debug_data', $debug_data, [Type::ARRAY,
                                                             Type::NULL,
        ]);
        $this->debug_data = $debug_data;

        return $this;
    }

    /**
     * @param string|null $msg
     *
     * @throws Ex\InvalidTypeException
     */
    public function withMessage(string $msg = null): self
    {
        Validator::assertIsType('message', $msg, [Type::STRING,
                                                  Type::NULL,
        ]);
        $this->message = $msg;

        return $this;
    }

    /**
     * @param array|null $placeholders
     */
    public function withPlaceholders(array $placeholders = null): self
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    /**
     * @param array|null $http_headers
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
     * @throws Ex\ArrayWithMixedKeysException
     * @throws Ex\ConfigurationNotFoundException
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\InvalidTypeException
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\NotIntegerException
     */
    public function build(): HttpResponse
    {
        $api_code = $this->api_code;
        Validator::assertIsInt('api_code', $api_code);

        $msg_or_api_code = $this->message ?? $api_code;
        $http_headers = $this->http_headers ?? [];

        if ($this->success) {
            $http_code = $this->http_code ?? RB::DEFAULT_HTTP_CODE_OK;

            Validator::assertOkHttpCode($http_code);

            $result = $this->make($this->success, $api_code,
                $msg_or_api_code, $this->data, $http_code,
                $this->placeholders, $http_headers, $this->json_opts);
        } else {
            $http_code = $this->http_code ?? RB::DEFAULT_HTTP_CODE_ERROR;

            Validator::assertErrorHttpCode($http_code);

            $result = $this->make(false, $api_code, $msg_or_api_code,
                $this->data, $http_code, $this->placeholders,
                $this->http_headers, $this->json_opts, $this->debug_data);
        }

        return $result;
    }

    /**
     * @param boolean        $success            TRUE if response reports successful operation,
     *                                           FALSE otherwise.
     * @param integer        $api_code           API code to be returned with the response object.
     * @param string|integer $msg_or_api_code    Message string or valid API code to get message for
     * @param mixed|null     $data               optional additional data to be included in response.
     * @param integer|null   $http_code          HTTP code for the HttpResponse or @null for either
     *                                           DEFAULT_HTTP_CODE_OK or DEFAULT_HTTP_CODE_ERROR
     *                                           depending on the $success.
     * @param array|null     $placeholders       Placeholders passed to Lang::get() for message
     *                                           placeholders substitution or @null if none.
     * @param array|null     $http_headers       Optional HTTP headers to be returned in the response.
     * @param integer|null   $json_opts          See http://php.net/manual/en/function.json-encode.php
     *                                           for supported options or pass @null to use value from
     *                                           your config (or defaults).
     * @param array|null     $debug_data         Optional debug data array to be added to returned JSON.
     *
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\ConfigurationNotFoundException
     * @throws Ex\ArrayWithMixedKeysException
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\InvalidTypeException
     * @throws Ex\NotIntegerException
     * @throws Ex\NotStringException
     *
     * @noinspection PhpTooManyParametersInspection
     */
    protected function make(bool  $success, int $api_code, $msg_or_api_code, $data = null,
                            int   $http_code = null, array $placeholders = null,
                            array $http_headers = null,
                            int   $json_opts = null, array $debug_data = null): HttpResponse
    {
        $http_headers = $http_headers ?? [];
        $http_code = $http_code ?? ($success ? RB::DEFAULT_HTTP_CODE_OK : RB::DEFAULT_HTTP_CODE_ERROR);
        /** @var int $json_opts */
        $json_opts = $json_opts ?? Config::get(RB::CONF_KEY_ENCODING_OPTIONS, RB::DEFAULT_ENCODING_OPTIONS);

        Validator::assertIsInt('encoding_options', $json_opts);

        Validator::assertIsInt('api_code', $api_code);
        if (!BaseApiCodes::isCodeValid($api_code)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            Validator::assertIsIntRange('api_code', $api_code, BaseApiCodes::getMinCode(), BaseApiCodes::getMaxCode());
        }

        return Response::json(
            $this->buildResponse($success, $api_code, $msg_or_api_code, $placeholders, $data, $debug_data),
            $http_code, $http_headers, $json_opts);
    }

    /**
     * Creates standardised API response array. This is final method called in the whole pipeline
     * before we return final JSON back to client. If you want to manipulate your response, this
     * is the place to do that. If you set APP_DEBUG to true, 'code_hex' field will be additionally
     * added to reported JSON for easier manual debugging. Returns response ready to be encoded as
     * JSON and sent back to client.
     *
     * @param boolean        $success         TRUE if response reports successful operation, FALSE otherwise.
     * @param integer        $api_code        Your API code to be returned with the response object.
     * @param string|integer $msg_or_api_code Message string or valid API code to get message for.
     * @param array|null     $placeholders    Placeholders passed to Lang::get() for message placeholders
     *                                        substitution or @null if none.
     * @param mixed|null     $data            API response data if any
     * @param array|null     $debug_data      optional debug data array to be added to returned JSON.
     *
     * @throws Ex\ArrayWithMixedKeysException
     * @throws Ex\ConfigurationNotFoundException
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\InvalidTypeException
     *
     * @noinspection PhpTooManyParametersInspection
     */
    protected function buildResponse(bool       $success,
                                     int        $api_code,
                                     string|int $msg_or_api_code,
                                     ?array     $placeholders = null,
                                     mixed      $data = null,
                                     ?array     $debug_data = null): array
    {
        // ensure $data is either @null, array or object of class with configured mapping.
        $data = (new Converter())->convert($data);
        if ($data !== null) {
            // ensure we get object in final JSON structure in data node
            $data = (object)$data;
        }

        if ($data === null && Config::get(RB::CONF_KEY_DATA_ALWAYS_OBJECT, false)) {
            $data = (object)[];
        }

        // get human readable message for API code or use message string (if given instead of API code)
        if (\is_int($msg_or_api_code)) {
            $message = $this->getMessageForApiCode($success, $msg_or_api_code, $placeholders);
        } else {
            Validator::assertIsType('message', $msg_or_api_code, [Type::STRING, Type::INTEGER]);
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
     * If $msg_or_api_code is integer value, returns human readable message associated with that code
     * (with fallback to built-in default string if no api code mapping is set. If $msg_or_api_code
     * is a string,  returns it unaltered.
     *
     * @param boolean    $success      TRUE if response reports successful operation, FALSE otherwise.
     * @param integer    $api_code     Your API code to be returned with the response object.
     * @param array|null $placeholders Placeholders passed to Lang::get() for message placeholders
     *                                 substitution or NULL if none.
     *
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\InvalidTypeException
     * @throws Ex\NotIntegerException
     */
    protected function getMessageForApiCode(bool  $success, int $api_code,
                                            array $placeholders = null): string
    {
        // We got integer value here not a message string, so we need to check if we have the mapping for
        // this string already configured.
        $key = BaseApiCodes::getCodeMessageKey($api_code);
        if ($key === null) {
            // nope, let's get the default one instead, based of
            $fallback_code = $success ? BaseApiCodes::OK() : BaseApiCodes::NO_ERROR_MESSAGE();
            // default messages are expected to be always available
            /** @var string $key */
            $key = BaseApiCodes::getCodeMessageKey($fallback_code);
        }

        $placeholders = $placeholders ?? [];
        if (!\array_key_exists('api_code', $placeholders)) {
            $placeholders['api_code'] = $api_code;
        }

        // As Lang::get() is documented to also returning whole language arrays,
        // so static analysers will alarm if that case is not taken care of.
        $msg = \Lang::get($key, $placeholders);
        if (\is_array($msg)) {
            $msg = \implode('', $msg);
        }

        return $msg;
    }

} // end of class
