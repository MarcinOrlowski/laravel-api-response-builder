<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2025 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */
class ApiResponse
{
    // ---------------------------------------------------------------------------------------------

    protected function __construct()
    {
        // nothing really
    }

    public static function fromJson(string $jsonString): self
    {
        /** @var array<string, mixed> $decoded_json */
        $decoded_json = \json_decode($jsonString, true, 32, JSON_THROW_ON_ERROR);

        // Ensure all response elements are present.
        /** @var array<string, array<string>> $keys */
        $keys = [
            ResponseBuilder::KEY_SUCCESS => [Type::BOOLEAN],
            ResponseBuilder::KEY_CODE    => [Type::INTEGER],
            ResponseBuilder::KEY_MESSAGE => [Type::STRING],
            ResponseBuilder::KEY_LOCALE  => [Type::STRING],
            ResponseBuilder::KEY_DATA    => [Type::ARRAY, Type::NULL],
        ];
        foreach ($keys as $key => $allowed_types) {
            if (!\array_key_exists($key, $decoded_json)) {
                throw new \InvalidArgumentException("Missing key '$key' in JSON response.");
            }
            Validator::assertIsType($key, $decoded_json[$key], $allowed_types);
        }

        // Ensure certain elements are not empty.
        $key = ResponseBuilder::KEY_LOCALE;
        if (empty($decoded_json[ $key ])) {
            throw new \InvalidArgumentException(
                "The '{$key}' in JSON response cannot be empty.");
        }

        $key = ResponseBuilder::KEY_MESSAGE;
        if (\is_null($decoded_json[ $key ])) {
            throw new \InvalidArgumentException(
                "The '{$key}' in JSON response cannot be NULL.");
        }

        /** @var bool $success */
        $success = $decoded_json[ResponseBuilder::KEY_SUCCESS];
        /** @var int $code */
        $code = $decoded_json[ResponseBuilder::KEY_CODE];
        /** @var string $message */
        $message = $decoded_json[ResponseBuilder::KEY_MESSAGE];
        /** @var string $locale */
        $locale = $decoded_json[ResponseBuilder::KEY_LOCALE];
        /** @var array<string, mixed>|null $data */
        $data = $decoded_json[ResponseBuilder::KEY_DATA];

        $api = (new self())
            ->setSuccess($success)
            ->setCode($code)
            ->setMessage($message)
            ->setLocale($locale)
            ->setData($data);

        // Optional debug data
        if (\array_key_exists(ResponseBuilder::KEY_DEBUG, $decoded_json)) {
            /** @var array<string, mixed>|null $debug */
            $debug = $decoded_json[ResponseBuilder::KEY_DEBUG];
            $api->setDebug($debug);
        }

        return $api;
    }

    // ---------------------------------------------------------------------------------------------

    protected bool $success;

    public function success(): bool
    {
        return $this->success;
    }

    protected function setSuccess(bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    // ---------------------------------------------------------------------------------------------

    protected int $code;

    public function getCode(): int
    {
        return $this->code;
    }

    protected function setCode(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    // ---------------------------------------------------------------------------------------------

    protected string $message;

    public function getMessage(): string
    {
        return $this->message;
    }

    protected function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    // ---------------------------------------------------------------------------------------------

    protected string $locale;

    public function getLocale(): string
    {
        return $this->locale;
    }

    protected function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    // ---------------------------------------------------------------------------------------------

    /** @var array<string, mixed>|null */
    protected ?array $data;

    /**
     * @return array<string, mixed>|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed>|null $data
     */
    protected function setData(?array $data): self
    {
        $this->data = $data;
        return $this;
    }

    // ---------------------------------------------------------------------------------------------

    /** @var array<string, mixed>|null */
    protected ?array $debug = null;

    /**
     * @return array<string, mixed>|null
     */
    public function getDebug(): ?array
    {
        return $this->debug;
    }

    /**
     * @param array<string, mixed>|null $debug
     */
    public function setDebug(?array $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    // ---------------------------------------------------------------------------------------------

} // end of class
