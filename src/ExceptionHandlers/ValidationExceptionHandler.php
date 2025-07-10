<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\ExceptionHandlers;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2025 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Contracts\ExceptionHandlerContract;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Handles ValidationException
 */
final class ValidationExceptionHandler implements ExceptionHandlerContract
{
    /**
     * @param array<string, mixed>      $user_config
     * @param \Throwable $ex
     * @return array<string, mixed>
     *
     * @throws \MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException
     * @throws \MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException
     * @throws \MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException
     *
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceAfterLastUsed
     */
    public function handle(array $user_config, \Throwable $ex): array
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $result = [
            RB::KEY_API_CODE  => BaseApiCodes::EX_VALIDATION_EXCEPTION(),
            RB::KEY_HTTP_CODE => HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
        ];
        /** @var array{api_code: int, http_code: 422} $result */
        return $result;
    }

} // end of class
