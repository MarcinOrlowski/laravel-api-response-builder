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
class ApiResponse
{
    // ---------------------------------------------------------------------------------------------

    protected function __construct()
    {
        // nothing really
    }

    public static function fromJson(string $jsonString): self
    {
        /** @var array $decoded_json */
        $decoded_json = \json_decode($jsonString, true, 32, JSON_THROW_ON_ERROR);

        // Ensure all response elements are present.
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
            /** @var mixed|null $allowed_types */
            if (!empty($allowed_types)) {
                /** @var array $allowed_types */
                Validator::assertIsType($key, $decoded_json[ $key ], $allowed_types);
            }
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

        $api = (new self())
            ->setSuccess($decoded_json[ ResponseBuilder::KEY_SUCCESS ])
            ->setCode($decoded_json[ ResponseBuilder::KEY_CODE ])
            ->setMessage($decoded_json[ ResponseBuilder::KEY_MESSAGE ])
            ->setLocale($decoded_json[ ResponseBuilder::KEY_LOCALE ])
            ->setData($decoded_json[ ResponseBuilder::KEY_DATA ]);

        // Optional debug data
        if (\array_key_exists(ResponseBuilder::KEY_DEBUG, $decoded_json)) {
            $api->setDebug($decoded_json[ ResponseBuilder::KEY_DEBUG ]);
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

    protected ?array $data;

    public function getData(): ?array
    {
        return $this->data;
    }

    protected function setData(?array $data): self
    {
        $this->data = $data;
        return $this;
    }

    // ---------------------------------------------------------------------------------------------

    protected ?array $debug = null;

    public function getDebug(): ?array
    {
        return $this->debug;
    }

    public function setDebug(?array $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    // ---------------------------------------------------------------------------------------------

} // end of class
