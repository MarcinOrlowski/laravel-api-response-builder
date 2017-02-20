# Exception Handling with Response Builder #

Properly designed REST API should never hit consumer with anything but JSON. While it looks like easy task, 
there's always chance for unexpected issue to occur. So we need to expect unexpected and be prepared when it
hit the fan. This means not only things like uncaught exception but also Laravel's maintenance mode can pollute
returned API responses which is unfortunately pretty common among badly written APIs. Do not be one of them, 
and take care of that in advance with couple of easy steps. In Laravel, unexpected situations are routed to 
Exception Handler. Unfortunately default implementation is not JSON API friendly, therefore `ResponseBuilder` 
provides drop-in replacement for Laravel's handler. Once installed, it ensures only JSON response will be 
returned no matter what happens.


## Using Exception Handler Helper ##

To make it works, edit `app/Exceptions/Handler.php` file, and add

    use MarcinOrlowski\ResponseBuilder\ExceptionHandlerHelper;

Next, modify handler's `render()` method body to ensure it calls calls 
our ExceptionHandlerHelper's. Default handler as of Laravel 5.2 has been
significantly simplified, and by default it looks like this:

    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }

After your edit it shall look like this:

    public function render($request, Exception $e)
    {
        return ExceptionHandlerHelper::render($request, $e);
    }

That's it. From now on, in case of any troubles, regular and standardized JSON responses will be
returned by your API instead of HTML page.


## Error codes ##

Since v1.6, ExceptionHandlerHelper can be used out of the box as it requires no extra configuration,
however it's strongly recommended you at least assign your own error codes for the events it handles,
so you will know what module in your code thrown the exception. For consistency I recommend
doing so even if you have just one module and do not chain APIs.

First edit your `ErrorCode` class (that one which stores your error codes) and define
four codes **within your allowed code range** (constants can be named as you like), representing
errors ExceptionHandlerHelper handles:

    const HTTP_NOT_FOUND = ...;
    const HTTP_SERVICE_UNAVAILABLE = ...;
    const HTTP_EXCEPTION = ...;
    const UNCAUGHT_EXCEPTION = ...;

then edit `config/response_builder.php` file to map exception types to your codes:

	'exception_handler' => [
		'exception' => [
			'http_not_found'           => ['code' => ApiCodeBase::HTTP_NOT_FOUND],
			'http_service_unavailable' => ['code' => ApiCodeBase::HTTP_SERVICE_UNAVAILABLE],
			'http_exception'           => ['code' => ApiCodeBase::HTTP_EXCEPTION],
			'uncaught_exception'       => ['code' => ApiCodeBase::UNCAUGHT_EXCEPTION],
		],
    ],

## HTTP return codes ##

You can also configure HTTP return code to use with each exception, by using `http_code` key:

    'http_not_found' => [
        'code'      => ApiCodeBase::UNKNOWN_METHOD,
        'http_code' => HttpResponse::HTTP_BAD_REQUEST,
    ],
    'http_service_unavailable' => [
        'code'      => ApiCodeBase::HTTP_SERVICE_UNAVAILABLE,
        'http_code' => HttpResponse::HTTP_BAD_REQUEST,
    ],
    'http_exception' => [
        'code'      => ApiCodeBase::HTTP_EXCEPTION,
        'http_code' => HttpResponse::HTTP_BAD_REQUEST,
    ],
    'uncaught_exception' => [
        'code'      => ApiCodeBase::UNCAUGHT_EXCEPTION,
        'http_code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
    ],

**NOTE:** you must use valid HTTP error code. Codes lower than 400 (`HttpResponse::HTTP_BAD_REQUEST`)
will be ignored.

Both keys `code` and `http_code` are optional and can be used selectively according to your needs.
Helper will fall back to defaults if these are not found:

    'http_not_found' => [
        'http_code' => HttpResponse::HTTP_BAD_REQUEST,
    ],
    'http_service_unavailable' => [
        'code' => ApiCodeBase::HTTP_SERVICE_UNAVAILABLE,
    ],
    'uncaught_exception' => [
    ],

Helper will try to use exception's status code if no dedicated `http_code` is configured but it would fall
to default `HttpResponse::HTTP_BAD_REQUEST` code (`HttpResponse::HTTP_INTERNAL_SERVER_ERROR` for uncaught
exceptions) if exceptions status code is `0`.

## Error messages ##

If you want to override built-in messages for any (or all) exceptions, edit `config/response_builder.php`
and add appropriate entry to `map` array:

	ApiCodeBase::HTTP_NOT_FOUND            => 'api.http_not_found',
	ApiCodeBase::HTTP_SERVICE_UNAVAILABLE  => 'api.http_service_unavailable',
	ApiCodeBase::HTTP_EXCEPTION            => 'api.http_exception',
	ApiCodeBase::UNCAUGHT_EXCEPTION        => 'api.uncaught_exception',

where `api.xxxx` entry must be valid localization string key from your app's localization strings
pool as per Lang's requirements. You can use placeholders in your messages. Supported are 
`:error_code` being substituted by actual error code assigned to this exception and `:message`
replaced by exception's `getMessage()` return value.

## Exceptions with messages ##

By default, messages obtained from `Exception` object have higher priority over configuration
mapped error messages, which makes return messages usually more descriptive. This behaviour can
be configured with `use_exception_message_first` option. When it's `true` (default)
**and** when exception's `getMessage()` returns non empty string, then that string will be 
used as returned as `message`. If it is set to `true` but exception provides no message,
then mapped message will be used and the `:message` placeholder will be substituted with
exception class name. When option is set to `false`, then mapped messages will always be used 
with `:message` placeholder being substituted with exception message (can if it is empty string).

## Exception Handler conflicts ##

Please note that some 3rd party packages may also provide own exception handling helpers and may 
recommend using said handlers in your application. Unfortunately this will cause conflict with
`ResponseBuilder`'s handler which usually lead to one (or another) handler not being executed
at all.

For example if your API delegates OAuth2 related tasks to popular [lucadegasperi/oauth2-server-laravel](https://packagist.org/packages/lucadegasperi/oauth2-server-laravel)
package, then you must **NOT** use its `OAuthExceptionHandlerMiddleware` class and ensure it is not set,
by inspecting `app/Kernel.php` file and ensuring the following line (if present):

    'LucaDegasperi\OAuth2Server\Middleware\OAuthExceptionHandlerMiddleware',

is removed or commented out.

## Notes ##

The above assumes you keep your codes in `ErrorCode` class stored in `app/ErrorCode.php` and using `App\ErrorCode` namespace.
