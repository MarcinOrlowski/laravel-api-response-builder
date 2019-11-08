<?php

/**
 * Laravel API Response Builder - configuration file
 *
 * See docs/config.md for detailed documentation
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

return [
    /*
    |-----------------------------------------------------------------------------------------------------------
    | Exception handler error codes
    |-----------------------------------------------------------------------------------------------------------
    |
    */
    'exception_handler' => [
        'map' => [
            /*
             * HTTP Exceptions
             * ---------------
             * Use this section to define how you want any Http Exception to be handled.
             * This means that you can define any Http code (i.e. 404 => HttpResponse::HTTP_NOT_FOUND)
             * and then configure what api_code should be returned to the user. If Http code
             * is not explicitely configured then `default` handler kicks in, and converts the exception
             * as best as it can.
             */
            HttpException::class => [
                // used by unauthenticated() to obtain api and http code for the exception
                HttpResponse::HTTP_UNAUTHORIZED         => [
                    'api_code'  => BaseApiCodes::EX_AUTHENTICATION_EXCEPTION(),
                    'http_code' => HttpResponse::HTTP_UNAUTHORIZED,
                ],
                // Required by ValidationException handler
                HttpResponse::HTTP_UNPROCESSABLE_ENTITY => [
                    'api_code'  => BaseApiCodes::EX_VALIDATION_EXCEPTION(),
                    'http_code' => HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
                ],
                // default handler is mandatory
                'default'                               => [
                    'api_code'  => BaseApiCodes::EX_HTTP_EXCEPTION(),
                    'http_code' => HttpResponse::HTTP_BAD_REQUEST,
                ],
            ],
            // This is final exception handler. If ex is not dealt with yet this is its last stop.
            // Default handler is mandatory.
            'default'            => [
                'api_code'  => BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
                'http_code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
            ],
        ],
    ],

];
