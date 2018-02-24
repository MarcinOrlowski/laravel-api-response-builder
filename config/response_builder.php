<?php

/**
 * Laravel API Response Builder - config file
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2018 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

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
	|    ApiCode::SOMETHING => 'api.something',
	|
	| See docs/exceptions.md if you want to provide own messages for built-in codes too.
	|
	*/
	'map' => [

	],


	/*
	|--------------------------------------------------------------------------
	| Response Builder classes
	|--------------------------------------------------------------------------
	|
	| Response Builder can auto-convert objects given as return $data. This is
	| handled by "classes" mapping. The key is class name to check object against,
	| and configuration elements include:
	|
	| "key" (string) (mandatory)
	|   - name of the key to be used in returned JSON after object is converted.
	|
	| "method" (string) (mandatory)
	|   - name of argument-less method to be called on the object for conversion
	|
	| See README for details
	|
	*/

	'classes' => [

		Illuminate\Database\Eloquent\Model::class => [
			'key'    => 'item',
			'method' => 'toArray',
		],

		Illuminate\Database\Eloquent\Collection::class => [
			'key'    => 'items',
			'method' => 'toArray',
		],

	],

	/*
	|--------------------------------------------------------------------------
	| data-to-json encodong options
	|--------------------------------------------------------------------------
	|
	| This controls data JSON encoding. Since 3.1, encoding was relying on
	| framework defaults, however this caused valid UTF8 characters (i.e. accents)
	| to be returned escaped, which while technically correct (ant theoretically
	| transparent) might not be desired effects.
	|
	| To prevent escaping, add JSON_UNESCAPED_UNICODE (default since v3.2):
	|    JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE
	|
	| Laravel's value:
	|    JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT
	|
	| See http://php.net/manual/en/function.json-encode.php for details
	|
	*/
	'encoding_options' => JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE,


	/*
	|--------------------------------------------------------------------------
	| Response JSON keys mapping
	|--------------------------------------------------------------------------
	|
	| You can remap response JSON keys if really needed.
	|
	|
	| WARNING: there's NO duplicate check at runtime, so if you remap two keys
	| to the same values you will end up with problems. There's testing trait
	| to prevent this from happening, so ensure you unit test your app (see docs!)
	|
	| NOTE: Ensure you have this config file using:
	|
	|    use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
	|
	| if you want to use custom mapping.
	|
	| It's safe to completely remove/comment out this config element.
	|
	*/
//	'response_key_map' => [
//		ResponseBuilder::KEY_SUCCESS => 'success',
//		ResponseBuilder::KEY_CODE    => 'code',
//		ResponseBuilder::KEY_LOCALE  => 'locale',
//		ResponseBuilder::KEY_MESSAGE => 'message',
//		ResponseBuilder::KEY_DATA    => 'data',
//	],

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

		// By default, exception provided messages has higher priority than mapped error messages.
		// This behaviour can be configured with `use_exception_message_first` option. When it
		// is set to `true` (which is default value) and when exception's `getMessage()` returns non empty
		// string, that string will be used and returned as `message` w/o further processing. If
		// it is set to `true` but exception provides no message, then mapped message will be used
		// and the ":message" placeholder will be substituted with exception class name. When option
		// is set to @false, then mapped messages will always be used with `:message` placeholder
		// being substituted with exception message (can if it is empty string).
		'use_exception_message_first' => env('EX_USE_EXCEPTION_MESSAGE', true),


		// Map exception to your own error codes. That way, when cascading
		// you will still know which module thrown this exception
		'exception' => [

//			'http_not_found' => [
//				'code'      => \App\ApiCodes::HTTP_NOT_FOUND,
//				'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_BAD_REQUEST,
//			],
//			'http_service_unavailable' => [
//				'code'      => \App\ApiCodes::HTTP_SERVICE_UNAVAILABLE,
//				'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_BAD_REQUEST,
//			],
//			'http_exception' => [
//				'code'      => \App\ApiCodes::HTTP_EXCEPTION,
//				'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_BAD_REQUEST,
//			],
//			'uncaught_exception' => [
//				'code'      => \App\ApiCodes::UNCAUGHT_EXCEPTION,
//				'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_INTERNAL_SERVER_ERROR,
//			],
//			'authentication_exception' => [
//				'code'      => \App\ApiCodes::AUTHENTICATION_EXCEPTION,
//				'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_UNAUTHORIZED,
//			],
//			'validation_exception' => [
//				'code'      => \App\ApiCodes::VALIDATION_EXCEPTION,
//				'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_UNPROCESSABLE_ENTITY,
//			],

		],

	],


	/*
	|--------------------------------------------------------------------------
	| Debug config
	|--------------------------------------------------------------------------
	|
	*/

	'debug' => [
//		'debug_key' => 'debug',

		'exception_handler' => [
			/**
			 * Name of the JSON key trace data should be put under in `debug` node.
			 */
//			'trace_key' => 'trace',

			/**
			 * When ExceptionHandler kicks in and this is set to @true,
			 * then returned JSON structure will contain additional debug data
			 * with information about class name, file name and line number.
			 */
			'trace_enabled' => env('APP_DEBUG', false),
		],
	],

];
