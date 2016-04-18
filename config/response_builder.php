<?php

/**
 * Laravel API Response Builder - config file
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use App\ErrorCode;

return [

	/*
	|--------------------------------------------------------------------------
	| Code range settings
	|--------------------------------------------------------------------------
	|
	| This option controls code range allowed error codes to use. This is
	| helpful when you use i.e. many chained APIs and you would like to ensure
	| all error codes are unique. By assigning different ranges to your API
	| and by properly setting min_code and max_code you have this guarded
	| by ResponseBuilder at runtime.
	|
	| NOTE ResponseBuilder reserves codes from 0 to 63 (inclusive) for own
	| internal use, and your codes cannot use this range.
	|
	| min_code - Min error code assigned for this module (inclusive)
	| max_code - Max error code assigned for this module (inclusive)
	|
	*/
	'min_code'  => 100,
    'max_code'  => 399,

	/*
	|--------------------------------------------------------------------------
	| Error code to message mapping
	|--------------------------------------------------------------------------
	|
	| ResponseBuilder automatically "translates" your error code to more human
	| readable form, that's why this mapping is needed. ResponseBuilder uses
	| standard Laravel's Lang
	|
	|    ErrorCode::SOMETHING => 'api.something',
	|
	| See README if you want to provide own messages for built-in codes too.
	|
	*/
    'map' => [

	    // Uncomment if you want to use ExceptionHandlerHelper helper

//	    ErrorCode::UNKNOWN_METHOD         => 'response-builder::builder.unknown_method',
//	    ErrorCode::SERVICE_IN_MAINTENANCE => 'response-builder::builder.service_in_maintenance',
//	    ErrorCode::HTTP_EXCEPTION         => 'response-builder::builder.http_exception_fmt',
//	    ErrorCode::UNCAUGHT_EXCEPTION     => 'response-builder::builder.uncaught_exception_fmt',

    ],


	/*
	|--------------------------------------------------------------------------
	| Exception handler error codes
	|--------------------------------------------------------------------------
	|
	| If you use ResponseBuilder's Exception handler helper, you must map events
	| to error codes you assigned.
	|
	| See README for details
	|
	*/
	'exception_handler' => [

//		'unknown_method'         => ErrorCode::UNKNOWN_METHOD,
//		'service_in_maintenance' => ErrorCode::SERVICE_IN_MAINTENANCE,
//		'http_exception'         => ErrorCode::HTTP_EXCEPTION,
//		'uncaught_exception'     => ErrorCode::UNCAUGHT_EXCEPTION,

	],

];
