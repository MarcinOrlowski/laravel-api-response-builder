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
	'no_error_message'             => 'Błąd #:error_code',

	// can be used by Exception Handler (if enabled)
	'uncaught_exception'           => 'Nieprzechwycony wyjątek: :message',
	'uncaught_exception_no_prefix' => ':message',
	'http_not_found'               => 'Nieznana metoda',
	'http_exception'               => 'Wyjątek HTTP: :message',
	'http_service_unavailable'     => 'Trwa przerwa serwisowa',

];

