<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Faker\Provider\Base;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Builds standardized HttpResponse response object
 */
class Builder
{
    /** @var bool */
    protected $success = false;

    /** @var int|null */
    protected $api_code;

    /** @var int|null */
    protected $http_code;

    /** @var mixed */
    protected $data;

    /** @var string */
    protected $message = null;

    /** @var array */
    protected $placeholders = [];

    /** @var int|null */
    protected $json_opts;

    /** @var array */
    protected $debug_data = [];

    /** @var array */
    protected $http_headers = [];

    /**
     * Private constructor. use success() and error() methods to obtain instance of Builder.
     *
     * @param bool $success
     * @param int  $api_code
     */
    protected function __construct(bool $success, int $api_code)
    {
        $this->success = $success;
        $this->api_code = $api_code ?? BaseApiCodes::OK();
    }

    public static function success(int $api_code = null): self
    {
        return new self(true, $api_code ?? BaseApiCodes::OK());
    }

    public static function error(int $api_code): self
    {
        $code_ok = BaseApiCodes::OK();
        if ($api_code !== $code_ok) {
            Validator::assertIntRange('api_code', $api_code, BaseApiCodes::getMinCode(), BaseApiCodes::getMaxCode());
        }
        if ($api_code === $code_ok) {
            throw new \InvalidArgumentException(
                "Error response cannot use api_code of value  {$code_ok} which is reserved for OK");
        }

        return new self(false, $api_code);
    }

    public function withHttpCode(int $http_code): self
    {
        $this->http_code = $http_code;

        return $this;
    }

    public function withData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function withJsonOptions(int $json_opts): self
    {
        Validator::assertInt('json_opts', $json_opts);
        $this->json_opts = $json_opts;

        return $this;
    }

    public function withDebugData(array $debug_data): self
    {
        $this->debug_data = $debug_data;

        return $this;
    }

    public function withMessage(string $msg): self
    {
        Validator::assertString('message', $msg);
        $this->message = $msg;

        return $this;
    }

    public function withHttpHeaders(array $http_headers): self
    {
        $this->http_headers = $http_headers;

        return $this;
    }

    public function build(): HttpResponse
    {
        if ($this->success) {
            $api_code = $this->api_code ?? BaseApiCodes::OK();
            $http_code = $this->http_code ?? ResponseBuilder::DEFAULT_HTTP_CODE_OK;

            $result = ResponseBuilderProxy::buildSuccessResponse(
                $this->data,
                $api_code,
                $this->placeholders,
                $http_code,
                $this->json_opts);
        } else {
            $http_code = $this->http_code ?? self::DEFAULT_HTTP_CODE_ERROR;
            $result = ResponseBuilderProxy::buildErrorResponse(
                $this->data,
                $api_code,
                $http_code,
                $this->placeholders,
                $this->message,
                $this->http_headers,
                $this->json_opts,
                $this->debug_data);
        }

        return $result;
    }
}
