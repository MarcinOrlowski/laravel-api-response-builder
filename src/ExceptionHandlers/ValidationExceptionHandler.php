<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\ExceptionHandlers;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
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
     * @param array      $user_config
     * @param \Throwable $ex
     *
     * @throws \MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException
     * @throws \MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException
     * @throws \MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException
     *
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceAfterLastUsed
     */
    public function handle(array $user_config, \Throwable $ex): ?array
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return [
            RB::KEY_API_CODE  => BaseApiCodes::EX_VALIDATION_EXCEPTION(),
            RB::KEY_HTTP_CODE => HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
        ];
    }

} // end of class
