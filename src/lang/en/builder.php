<?php

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-response-builder
 */
return [

	'ok'                           => 'OK',
	'no_error_message'             => 'Error #:error_code',

	// can be used by Exception Handler (if enabled)
	'uncaught_exception'           => 'Uncaught exception: :message',
	'uncaught_exception_no_prefix' => ':message',
	'http_not_found'               => 'Unknown method',
	'http_exception'               => 'HTTP exception: :message',
	'http_service_unavailable'     => 'Service maintenance in progress',

];

