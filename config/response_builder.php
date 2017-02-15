<?php

/**
 * Laravel API Response Builder - config file
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use App\ErrorCode;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

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

	],


	/*
	|--------------------------------------------------------------------------
	| Response Builder classes
	|--------------------------------------------------------------------------
	|
	| Response Builder includes handling of object of given classes (i.e. Eloquent
	| Model or Collection). Some of these require some additional settings
	| and here you have option to override the defaults
	|
	| See README for details
	|
	*/

	'classes' => [

		Illuminate\Database\Eloquent\Model::class => [
			'key' => 'item',
		],

		Illuminate\Database\Eloquent\Collection::class => [
			'key' => 'items',
		],

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

		// By default, exception provided messages have higher priority than mapped error messages.
		// This behaviour can be configured with `use_exception_message_first` option. When option
		// is set to `true` (which is default value) and when exception's `getMessage()` returns non empty
		// string, that string will be used as returned as `message` w/o further processing. If
		// it is set to `true` but exception provides no message, then mapped message will be used
		// and the ":message" placeholder will be substituted with exception class name. When option
		// is set to @false, then pre 2.0 behaviour takes place and mapped messages will always be used
		// with `:message` placeholder being substituted with exception message (can if it is empty string).
		'use_exception_message_first' => env('EX_USE_EXCEPTION_MESSAGE', true),


		// Map exception to your own error codes. That way, when cascading
		// you will still know which module thrown this exception
		'exception' => [
//			'http_not_found' => [
//				'code'      => ErrorCode::HTTP_NOT_FOUND,
//				'http_code' => HttpResponse::HTTP_BAD_REQUEST,
//			],
//			'http_service_unavailable' => [
//				'code'      => ErrorCode::HTTP_SERVICE_UNAVAILABLE,
//				'http_code' => HttpResponse::HTTP_BAD_REQUEST,
//			],
//			'http_exception' => [
//				'code'      => ErrorCode::HTTP_EXCEPTION,
//				'http_code' => HttpResponse::HTTP_BAD_REQUEST,
//			],
//			'uncaught_exception' => [
//				'code'      => ErrorCode::UNCAUGHT_EXCEPTION,
//				'http_code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
//			],
		],

	],

];
