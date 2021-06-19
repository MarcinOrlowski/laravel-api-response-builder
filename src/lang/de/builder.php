<?php
declare(strict_types=1);

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2021 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-response-builder
 */
return [

    'ok'                       => 'OK',
    'no_error_message'         => 'Fehler #:api_code',

    // Used by Exception Handler Helper (when used)
    'uncaught_exception'       => 'Ungefangene Ausnahme: :message',
    'http_exception'           => 'HTTP Ausnahme: :message',

    // HttpException handler (added in 6.4.0)
    // Error messages for HttpException caught w/o custom messages
    // https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    //
    // German translation based on https://wiki.selfhtml.org/wiki/HTTP/Statuscodes
    'http_400'                 => 'Ungültige Anfrage',
    'http_401'                 => 'Unautorisiert',
    'http_402'                 => 'Bezahlung benötigt',
    'http_403'                 => 'Verboten',
    'http_404'                 => 'Nicht gefunden',
    'http_405'                 => 'Methode nicht erlaubt',
    'http_406'                 => 'Nicht akzeptabel',
    'http_407'                 => 'Proxy-Authentifizierung benötigt',
    'http_408'                 => 'Anfrage-Zeitüberschreitung',
    'http_409'                 => 'Konflikt',
    'http_410'                 => 'Verschwunden',
    'http_411'                 => 'Länge benötigt',
    'http_412'                 => 'Vorbedingung missglückt',
    'http_413'                 => 'Anfrage-Entität zu groß',
    'http_414'                 => 'Anfrage-URI zu lang',
    'http_415'                 => 'Nicht unterstützter Medientyp',
    'http_416'                 => 'Anfrage-Bereich nicht erfüllbar',
    'http_417'                 => 'Erwartung missglückt',
    'http_421'                 => 'Fehlgeleitete Anforderung',
    'http_422'                 => 'Kann nicht verarbeitet werden',
    'http_423'                 => 'Gesperrt',
    'http_424'                 => 'Vorhergehende Bedingung nicht erfüllt',
    'http_425'                 => 'Too Early',  // FIXME
    'http_426'                 => 'Update benötigt',
    'http_428'                 => ' Vorbedingung benötigt',
    'http_429'                 => 'Zu viele Anfragen',
    'http_431'                 => 'Headerfelds zu groß',
    'http_451'                 => 'Ressource aus rechtlichen Gründen nicht verfügbar',

    'http_500'                 => 'Interner Server-Fehler',
    'http_501'                 => 'Nicht implementiert',
    'http_502'                 => 'Schlechtes Portal',
    'http_503'                 => 'Dienst nicht verfügbar',
    'http_504'                 => 'Portal-Auszeit',
    'http_505'                 => 'HTTP-Version nicht unterstützt',
    'http_506'                 => 'Variant Also Negotiates',  // FIXME
    'http_507'                 => 'Speicher des Servers reicht nicht aus',
    'http_508'                 => 'Endlosschleife',
    'http_509'                 => 'Unassigned',     // FIXME
    'http_510'                 => 'Zu wenig Informationen',
    'http_511'                 => 'Identizifierung benötigt',
];

