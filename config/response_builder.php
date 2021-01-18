<?php

/**
 * Laravel API Response Builder - configuration file
 *
 * See docs/config.md for detailed documentation
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2021 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

return [
	/*
	|-------------------------------------------------------------------------------------------------------------------
	| Code range settings
	|-------------------------------------------------------------------------------------------------------------------
	*/
	'min_code'          => 100,
	'max_code'          => 1024,

	/*
	|-------------------------------------------------------------------------------------------------------------------
	| Error code to message mapping
	|-------------------------------------------------------------------------------------------------------------------
	*/
	'map'               => [
//         YOUR_API_CODE => '<MESSAGE_LOCALISATION_KEY>',
	],

	/*
	|-------------------------------------------------------------------------------------------------------------------
	| Response Builder data converter
	|-------------------------------------------------------------------------------------------------------------------
	*/
	'converter'         => [
		'primitives' => [
			/*
			|-----------------------------------------------------------------------------------------------------------
			| Configuration for primitives used when such data is passed directly as payload (i.e. `success(15)`;)
			|-----------------------------------------------------------------------------------------------------------
			*/
			'array'   => [
				'key' => 'values',
			],
			'boolean' => [
				'key' => 'value',
			],
			'double'  => [
				'key' => 'value',
			],
			'integer' => [
				'key' => 'value',
			],
			'string'  => [
				'key' => 'value',
			],
		],

		/*
		|-----------------------------------------------------------------------------------------------------------
		| Object converters configuration for supported classes
		|-----------------------------------------------------------------------------------------------------------
		*/
		'classes'    => [
			\Illuminate\Database\Eloquent\Model::class          => [
				'handler' => \MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter::class,
				'key'     => 'item',
				'pri'     => 0,
			],
			\Illuminate\Support\Collection::class               => [
				'handler' => \MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter::class,
				'key'     => 'items',
				'pri'     => 0,
			],
			\Illuminate\Database\Eloquent\Collection::class     => [
				'handler' => \MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter::class,
				'key'     => 'items',
				'pri'     => 0,
			],
			\Illuminate\Http\Resources\Json\JsonResource::class => [
				'handler' => \MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter::class,
				'key'     => 'item',
				'pri'     => 0,
			],

			/*
			|-----------------------------------------------------------------------------------------------------------
			| Paginators converts to objects already, so we do not array wrapping here, hence setting `key` to null
			|-----------------------------------------------------------------------------------------------------------
			*/
			\Illuminate\Pagination\LengthAwarePaginator::class  => [
				'handler' => \MarcinOrlowski\ResponseBuilder\Converters\ArrayableConverter::class,
				'key'     => null,
				'pri'     => 0,
			],
			\Illuminate\Pagination\Paginator::class             => [
				'handler' => \MarcinOrlowski\ResponseBuilder\Converters\ArrayableConverter::class,
				'key'     => null,
				'pri'     => 0,
			],

			/*
			|-----------------------------------------------------------------------------------------------------------
			| Generic converters should have lower pri to allow dedicated ones to kick in first when class matches
			|-----------------------------------------------------------------------------------------------------------
			*/
			\JsonSerializable::class                            => [
				'handler' => \MarcinOrlowski\ResponseBuilder\Converters\JsonSerializableConverter::class,
				'key'     => 'item',
				'pri'     => -10,
			],
			\Illuminate\Contracts\Support\Arrayable::class      => [
				'handler' => \MarcinOrlowski\ResponseBuilder\Converters\ArrayableConverter::class,
				'key'     => 'items',
				'pri'     => -10,
			],
		],

	],

	/*
	|-------------------------------------------------------------------------------------------------------------------
	| Exception handler error codes
	|-------------------------------------------------------------------------------------------------------------------
	*/
	'exception_handler' => [
		/*
		 * The following keys are supported for each handler specified.
		 *   `handler`
		 *   `pri`
		 *   `config`
		 *
		 * The following keys are supported in "config" entry for each handler specified:
		 *   `api_code`   : (int) mandatory api_code to be used for given exception
		 *   `http_code`  : (int) optional HTTP code. If not specified, exception's HTTP status code will be used.
		 *   `msg_key`    : (string) optional localization string key (ie. 'app.my_error_string') which will be used
		 *                  if exception's message is empty. If `msg_key` is not provided, ExceptionHandler will
		 *                  fall back to built-in message, with message key built as "http_XXX" where XXX is
		 *                  HTTP code used to handle given the exception.
		 *   `msg_enforce`: (boolean) if `true`, then fallback message (either one specified with `msg_key`, or
		 *                  built-in one will **always** be used, ignoring exception's message string completely.
		 *                  If set to `false` (default) then it will enforce either built-in message (if no
		 *                  `msg_key` is set, or message referenced by `msg_key` completely ignoring exception
		 *                  message ($ex->getMessage()).
		 */

		\Illuminate\Validation\ValidationException::class => [
			'handler' => \MarcinOrlowski\ResponseBuilder\ExceptionHandlers\ValidationExceptionHandler::class,
			'pri'     => -100,
			'config'  => [
//		        'api_code'  => ApiCodes::YOUR_API_CODE_FOR_VALIDATION_EXCEPTION,
//		        'http_code' => HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
			],
		],

		\Symfony\Component\HttpKernel\Exception\HttpException::class => [
			'handler' => \MarcinOrlowski\ResponseBuilder\ExceptionHandlers\HttpExceptionHandler::class,
			'pri'     => -100,
			'config'  => [
//		        HttpException::class => [
//			        // used by unauthenticated() to obtain api and http code for the exception
//			        HttpResponse::HTTP_UNAUTHORIZED         => [
//				        'api_code' => ApiCodes::YOUR_API_CODE_FOR_UNATHORIZED_EXCEPTION,
//			        ],
//			        // Required by ValidationException handler
//			        HttpResponse::HTTP_UNPROCESSABLE_ENTITY => [
//				        'api_code' => ApiCodes::YOUR_API_CODE_FOR_VALIDATION_EXCEPTION,
//			        ],
//			        // default handler is mandatory and MUST have both `api_code` and `http_code` set.
//			        'default'                               => [
//				        'api_code'  => ApiCodes::YOUR_API_CODE_FOR_GENERIC_HTTP_EXCEPTION,
//				        'http_code' => HttpResponse::HTTP_BAD_REQUEST,
//			        ],
//		        ],
			],

			/*
			|-----------------------------------------------------------------------------------------------------------
			| This is final exception handler. If ex is not dealt with yet this is its last stop.
			| Default handler is mandatory and MUST have both `api_code` and `http_code` set.
			|-----------------------------------------------------------------------------------------------------------
			*/
			'default' => [
				'handler' => \MarcinOrlowski\ResponseBuilder\ExceptionHandlers\HttpExceptionHandler::class,
				'pri'     => -127,
				'config'  => [
//			        'api_code'  => ApiCodes::YOUR_API_CODE_FOR_UNHANDLED_EXCEPTION,
//			        'http_code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
				],
			],
		],
	],

	/*
	|-------------------------------------------------------------------------------------------------------------------
	| data-to-json encoding options
	|-------------------------------------------------------------------------------------------------------------------
	*/
	'encoding_options'  => JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE,

	/*
	|-------------------------------------------------------------------------------------------------------------------
	| Debug config
	|-------------------------------------------------------------------------------------------------------------------
	*/
	'debug'             => [
		'debug_key'         => 'debug',
		'exception_handler' => [
			'trace_key'     => 'trace',
			'trace_enabled' => env('APP_DEBUG', false),
		],

		// Controls debugging features of payload converter class.
		'converter'         => [
			// Set to true to figure out what converter is used for given data payload and why.
			'debug_enabled' => env('RB_CONVERTER_DEBUG', false),
		],
	],

];
