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
