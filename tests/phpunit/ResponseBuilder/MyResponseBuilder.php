<?php
declare(strict_types=1);
/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace MarcinOrlowski\ResponseBuilder\Tests\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */
class MyResponseBuilder extends \MarcinOrlowski\ResponseBuilder\ResponseBuilder
{
    public static array $fake_response = [];

    /**
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
