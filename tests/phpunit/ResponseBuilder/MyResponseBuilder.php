<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2025 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
class MyResponseBuilder extends \MarcinOrlowski\ResponseBuilder\ResponseBuilder
{
    /** @var array<string, mixed> */
    public static array $fake_response = [];

    /**
     * @return array<string, mixed>
     *
     * @noinspection PhpMissingParentCallCommonInspection
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed
     */
    protected function buildResponse(bool       $success,
                                     int        $api_code,
                                     int|string $msg_or_api_code,
                                     ?array     $placeholders = null,
                                     mixed      $data = null,
                                     ?array     $debug_data = null): array
    {
        return static::$fake_response;
    }

} // end of class
