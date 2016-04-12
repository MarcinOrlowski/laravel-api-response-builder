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

	'ok'                   => 'OK',
	'no_error_message_fmt' => 'Błąd #:error_code',

	// can be used by Exception Handler (if enabled)
	'uncaught_exception_fmt'  => 'Nieprzechwycony wyjątek: :message',
	'unknown_method'          => 'Nieznana metoda',
	'http_exception_fmt'      => 'Wyjątek HTTP: :message',
	'service_in_maintenance'  => 'Trwa przerwa serwisowa',

];

