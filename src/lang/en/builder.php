<?php

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-response-builder
 */
return [

	'ok'                       => 'OK',
	'no_error_message'         => 'Error #:api_code',

	// can be used by Exception Handler (if enabled)
	'uncaught_exception'       => 'Uncaught exception :message',
	// we talking API call here, hence we have http_not_found
	'http_not_found'           => 'Item not found',
	'http_exception'           => 'HTTP exception :message',
	'http_service_unavailable' => 'Service maintenance in progress',

	// Exception Handler (added in 3.3.0)
	'authentication_exception' => 'Not authorized to access',
	'validation_exception'     => 'Invalid data',

];

