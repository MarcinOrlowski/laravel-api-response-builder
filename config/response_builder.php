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
            'pri'    => 0,
        ],
        \Illuminate\Support\Collection::class               => [
            'key'    => 'items',
            'method' => 'toArray',
            'pri'    => 0,
        ],
        \Illuminate\Database\Eloquent\Collection::class     => [
            'key'    => 'items',
            'method' => 'toArray',
            'pri'    => 0,
        ],
        \Illuminate\Http\Resources\Json\JsonResource::class => [
            'key'    => 'item',
            'method' => 'toArray',
            'pri'    => 0,
        ],
    ],

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
             * Configure how you want Http Exception to be handled based on its Http status code.
             * For each code you need to define at least the `api_code` to be returned in final response.
             * Additionally, you can specify `http_code` to be any valid 400-599 HTTP status code, otherwise
             * code set in the exception will be used.
             */
//            HttpException::class => [
//                // used by unauthenticated() to obtain api and http code for the exception
//                HttpResponse::HTTP_UNAUTHORIZED         => [
//                    'api_code'  => ApiCodes::YOUR_API_CODE_FOR_UNATHORIZED_EXCEPTION,
//                ],
//                // Required by ValidationException handler
//                HttpResponse::HTTP_UNPROCESSABLE_ENTITY => [
//                    'api_code'  => ApiCodes::YOUR_API_CODE_FOR_VALIDATION_EXCEPTION,
//                ],
//                // default handler is mandatory and MUST have both `api_code` and `http_code` set.
//                'default'                               => [
//                    'api_code'  => ApiCodes::YOUR_API_CODE_FOR_GENERIC_HTTP_EXCEPTION,
//                    'http_code' => HttpResponse::HTTP_BAD_REQUEST,
//                ],
//            ],
//            // This is final exception handler. If ex is not dealt with yet this is its last stop.
//            // default handler is mandatory and MUST have both `api_code` and `http_code` set.
//            'default'            => [
//                'api_code'  => ApiCodes::YOUR_API_CODE_FOR_UNHANDLED_EXCEPTION,
//                'http_code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
//            ],
//        ],
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
