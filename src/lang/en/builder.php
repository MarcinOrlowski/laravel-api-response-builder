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
    'no_error_message'         => 'Error #:api_code',

    // Used by Exception Handler Helper (when used)
    'uncaught_exception'       => 'Uncaught exception: :message',
    'http_exception'           => 'HTTP exception: :message',

    // HttpException handler (added in 6.4.0)
    // Error messages for HttpException caught w/o custom messages
    // https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    'http_400'                 => 'Bad Request',
    'http_401'                 => 'Unauthorized',
    'http_402'                 => 'Payment Required',
    'http_403'                 => 'Forbidden',
    'http_404'                 => 'Not Found',
    'http_405'                 => 'Method Not Allowed',
    'http_406'                 => 'Not Acceptable',
    'http_407'                 => 'Proxy Authentication Required',
    'http_408'                 => 'Request Timeout',
    'http_409'                 => 'Conflict',
    'http_410'                 => 'Gone',
    'http_411'                 => 'Length Required',
    'http_412'                 => 'Precondition Failed',
    'http_413'                 => 'Payload Too Large',
    'http_414'                 => 'URI Too Long',
    'http_415'                 => 'Unsupported Media Type',
    'http_416'                 => 'Range Not Satisfiable',
    'http_417'                 => 'Expectation Failed',
    'http_421'                 => 'Misdirected Request',
    'http_422'                 => 'Unprocessable Entity',
    'http_423'                 => 'Locked',
    'http_424'                 => 'Failed Dependency',
    'http_425'                 => 'Too Early',
    'http_426'                 => 'Upgrade Required',
    'http_428'                 => 'Precondition Required',
    'http_429'                 => 'Too Many Requests',
    'http_431'                 => 'Request Header Fields Too Large',
    'http_451'                 => 'Unavailable For Legal Reasons',

    'http_500'                 => 'Internal Server Error',
    'http_501'                 => 'Not Implemented',
    'http_502'                 => 'Bad Gateway',
    'http_503'                 => 'Service Unavailable',
    'http_504'                 => 'Gateway Timeout',
    'http_505'                 => 'HTTP Version Not Supported',
    'http_506'                 => 'Variant Also Negotiates',
    'http_507'                 => 'Insufficient Storage',
    'http_508'                 => 'Loop Detected',
    'http_509'                 => 'Unassigned',
    'http_510'                 => 'Not Extended',
    'http_511'                 => 'Network Authentication Required',
];

