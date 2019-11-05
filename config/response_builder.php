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

use \MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use \Symfony\Component\HttpFoundation\Response as HttpResponse;

return [
    /*
    |-----------------------------------------------------------------------------------------------------------
    | Code range settings
    |-----------------------------------------------------------------------------------------------------------
    */
    'min_code'          => 100,
    'max_code'          => 1024,

    /*
    |-----------------------------------------------------------------------------------------------------------
    | Error code to message mapping
    |-----------------------------------------------------------------------------------------------------------
    |
    */
    'map'               => [
        // YOUR_API_CODE => '<MESSAGE_KEY>',
    ],

    /*
    |-----------------------------------------------------------------------------------------------------------
    | Response Builder classes
    |-----------------------------------------------------------------------------------------------------------
    |
    */
    'classes'           => [
        \Illuminate\Database\Eloquent\Model::class          => [
            'key'    => 'item',
            'method' => 'toArray',
        ],
        \Illuminate\Support\Collection::class               => [
            'key'    => 'items',
            'method' => 'toArray',
        ],
        \Illuminate\Database\Eloquent\Collection::class     => [
            'key'    => 'items',
            'method' => 'toArray',
        ],
        \Illuminate\Http\Resources\Json\JsonResource::class => [
            'key'    => 'item',
            'method' => 'toArray',
        ],
    ],

    /*
    |-----------------------------------------------------------------------------------------------------------
    | data-to-json encoding options
    |-----------------------------------------------------------------------------------------------------------
    |
    */
    'encoding_options'  => JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE,

    /*
    |-----------------------------------------------------------------------------------------------------------
    | Exception handler error codes
    |-----------------------------------------------------------------------------------------------------------
    |
    */
    'exception_handler' => [
        /*
         * HTTP Exceptions
         * Use this section to define how you want any Http Exception to be handled.
         * This means that you can define any Http code (i.e. 404 => HttpResponse::HTTP_NOT_FOUND)
         * and then configure what api_code should be returned back to the user. If Http code
         * is not explicitely configured then `default` handler kicks in, and converts it.
         */
//        \Symfony\Component\HttpKernel\Exception\HttpException::class => [
//            // used by unauthenticated() to obtain api and http code for the exception
//            HttpResponse::HTTP_UNAUTHORIZED => [
//                'api_code'  => <YOUR_API_CODE>,
//                'http_code' => HttpResponse::HTTP_UNAUTHORIZED,
//            ],
//
//            // Required by ValidationException handler
//            HttpResponse::HTTP_UNPROCESSABLE_ENTITY => [
//                'api_code'  => <YOUR_API_CODE>,
//                'http_code' => HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
//            ],
//            // default handler is mandatory
//            'default' => [
//                'api_code'  => BaseApiCodes::EX_HTTP_EXCEPTION(),
//                'http_code' => HttpResponse::HTTP_BAD_REQUEST,
//            ],
//        ],
//        // default handler is mandatory
//        'default' => [
//            'api_code'  => BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
//            'http_code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
//        ],
    ],

    /*
    |-----------------------------------------------------------------------------------------------------------
    | Debug config
    |-----------------------------------------------------------------------------------------------------------
    |
    */
    'debug'             => [
        'debug_key'         => 'debug',
        'exception_handler' => [
            'trace_key'     => 'trace',
            'trace_enabled' => env('APP_DEBUG', false),
        ],
    ],

];
