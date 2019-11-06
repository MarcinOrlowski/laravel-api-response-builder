<?php

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-response-builder
 */
return [

    'ok'                       => 'موفق',
    'no_error_message'         => 'خطای شماره :api_code',

    // can be used by Exception Handler (if enabled)
    'uncaught_exception'       => 'استثناء مدیریت نشده :message',
    // we talking API call here, hence we have http_not_found
    'http_not_found'           => 'مورد یافت نشد',
    'http_exception'           => 'استثناء HTTP :message',
    'http_service_unavailable' => 'عملیات نگهداری در حال انجام است',

    // Exception Handler (added in 3.3.0)
    'authentication_exception' => 'اجازه دسترسی ندارید',
    'validation_exception'     => 'داده معتبر نیست',

];

