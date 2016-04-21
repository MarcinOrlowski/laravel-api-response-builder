# Exception Handling with Response Builder #

Properly designed REST API should never hit consumer with plain string, HTML page nor anything but JSON.
While in regular use this is quite easy to achieve, unexpected problems like uncaught exception or
even enabled maintenance mode can still happen and pollute returned data of many APIs world wide.
Do not be one of them, and take care of that in advance with couple of easy steps. In Laravel, unexpected
situations are routed to Exception Handler. Default implementation is not JSON API friendly, therefore
Response Builder package provides drop-in replacement. Once installed, JSON response will still be
generated.


## Using Exception Handler Helper ##

To make it works simply edit `app/Exceptions/Handler.php` file, add

    use MarcinOrlowski\ResponseBuilder\ExceptionHandlerHelper;

Then modify handler's `render()` method body. so instead of parent class
method it calls ExceptionHandlerHelper's. Default handler as of Laravel
5.2 has been significantly simplified, and it should now look like this:

    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }

After edit it shall look like this:

    public function render($request, Exception $e)
    {
        return ExceptionHandlerHelper::render($request, $e);
    }

From now on, JSON responses will be returned by your API instead of HTML page.


## Error codes ##

Since v1.6, ExceptionHandlerHelper can be used out of the box as it requires no extra configuration,
however it's strongly recommended you at least assign your own error codes for the events it handles,
so you will know what module in your code thrown the exception. For consistency I recommend
doing so even if you have just one module and do not chain APIs.

First edit your `ErrorCode` class (that one which stores your error codes) and define
four codes **within your allowed code range** (constants can be named as you like), representing
errors ExceptionHandlerHelper can deal with:

    const HTTP_NOT_FOUND = ...;
    const HTTP_SERVICE_UNAVAILABLE = ...;
    const HTTP_EXCEPTION = ...;
    const UNCAUGHT_EXCEPTION = ...;

an then edit `config/response_builder.php` file to map exception types to your codes:

	'exception_handler' => [
		'exception' => [
			'http_not_found'           => ErrorCode::HTTP_NOT_FOUND,
			'http_service_unavailable' => ErrorCode::HTTP_SERVICE_UNAVAILABLE,
			'http_exception'           => ErrorCode::HTTP_EXCEPTION,
			'uncaught_exception'       => ErrorCode::UNCAUGHT_EXCEPTION,
		],
    ],

## Error messages ##

If you want to override built-in messages for any (or all) exceptions, edit `config/response_builder.php`
and add appropriate entry to `map` array:

	ErrorCode::HTTP_NOT_FOUND            => 'api.http_not_found',
	ErrorCode::HTTP_SERVICE_UNAVAILABLE  => 'api.http_service_unavailable',
	ErrorCode::HTTP_EXCEPTION            => 'api.http_exception',
	ErrorCode::UNCAUGHT_EXCEPTION        => 'api.uncaught_exception',

where `api.xxxx` entry must be valid localization string key from your app. You can use placeholders
in your messages. Supported are `:error_code` substituted by actual error code assigned to this
exception and `:message` substituted by content returned by exception's `getMessage()` method.


## Exception Handler conflicts ##

Please note that some packages may also provide own exception handling helpers and may recommend
enabling them. If you do so, it will conflict and our handler may not be executed at all.

For example if your API delegates OAuth2 related tasks to popular [lucadegasperi/oauth2-server-laravel](https://packagist.org/packages/lucadegasperi/oauth2-server-laravel)
package, then you must not use its `OAuthExceptionHandlerMiddleware`. To ensure it is not set,
edit `app/Kernel.php` file and remove/comment out the following line (if present):

    'LucaDegasperi\OAuth2Server\Middleware\OAuthExceptionHandlerMiddleware',


## Notes ##

The above assumes you keep your codes in `ErrorCode` class stored in `app/ErrorCode.php` and using `App\ErrorCode` namespace.
