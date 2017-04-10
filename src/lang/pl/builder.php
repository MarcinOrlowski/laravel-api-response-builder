<?php

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-response-builder
 */
return [

	'ok'                           => 'OK',
	'no_error_message'             => 'Błąd #:api_code',

	// can be used by Exception Handler (if enabled)
	'uncaught_exception'           => 'Nieprzechwycony wyjątek :message',
	'http_not_found'               => 'Nieznana metoda',
	'http_exception'               => 'Wyjątek HTTP :message',
	'http_service_unavailable'     => 'Trwa przerwa serwisowa',

	// Exception Handler (added in 3.3.0)
	'authentication_exception'     => 'Brak autoryzacji dostępu',
	'validation_exception'         => 'Nieprawidłowe dane',

];

