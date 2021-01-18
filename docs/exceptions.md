![REST API Response Builder for Laravel](img/logo.png)

# Exception Handling #

[« Documentation table of contents](README.md)

 * [Exception Handling with Response Builder](#exception-handling-with-response-builder)
   * [Using Exception Handler Helper](#using-exception-handler-helper)
   * [Error codes](#error-codes)
   * [HTTP return codes](#http-return-codes)
   * [Error messages](#error-messages)
   * [Important notes](#important-notes)
     * [Possible Exception Handler conflicts](#possible-exception-handler-conflicts)
     * [ApiCodes](#apicodes)

---

 > ![WARNING](img/warning.png) Use of provided `ExceptionHandler` helper **requires** additional, **manual**
 > installation steps to be made, otherwise Laravel's built-in handler will be used instead. See the details
 > documented below.

---

# Exception Handling with Response Builder #

 Properly designed REST API should never hit consumer with anything but JSON. While it looks like easy task, 
 there's always chance for unexpected issue to occur. So we need to expect unexpected and be prepared when it
 hit the fan. This means not only things like uncaught exception but also Laravel's maintenance mode can pollute
 returned API responses which is unfortunately pretty common among badly written APIs. Do not be one of them, 
 and take care of that in advance with couple of easy steps. In Laravel, unexpected situations are routed to 
 Exception Handler. Unfortunately default implementation is not JSON API friendly, therefore `ResponseBuilder` 
 provides drop-in replacement for Laravel's handler. Once installed, it ensures only JSON response will be 
 returned no matter what happens.
 
 > ![NOTE](img/notes.png) If you are intent to use Exception Handler helper, you **MUST** [configure](config.md) it first in
 > your config file (esp. `default` handler configuration)!

## Using Exception Handler Helper ##

 To make it works, edit `app/Exceptions/Handler.php` file, and add

```php
use MarcinOrlowski\ResponseBuilder\ExceptionHandlerHelper;
```

 Next, modify handler's `render()` method body to ensure it calls calls our `ExceptionHandlerHelper`'s.
 Default handler as of Laravel 5.2 has been significantly simplified and by default it looks like this:

```php
public function render($request, Throwable $e)
{
    return parent::render($request, $e);
}
```

 After your edit it shall look like this:

```php
public function render($request, Throwable $e)
{
    return ExceptionHandlerHelper::render($request, $e);
}
```

 From now on, in case of any troubles, regular and standardized JSON responses will be
 returned by your API instead of HTML page.


## Error codes ##

 `ExceptionHandlerHelper` can be used out of the box as it requires no extra configuration,
 however it's strongly recommended you at least assign your own api codes for the events it handles,
 so you will know what module in your code thrown the exception. For consistency, I recommend
 doing so even if you have just one module and do not chain APIs.

 First edit your `ApiCodes` class (that one which stores **your** API return code constants) and define
 codes **within your allowed code range** (constants can be named as you like), representing
 cases `ExceptionHandlerHelper` handles:

```php
public const HTTP_NOT_FOUND = ...;
public const HTTP_SERVICE_UNAVAILABLE = ...;
public const HTTP_EXCEPTION = ...;
public const UNCAUGHT_EXCEPTION = ...;
public const AUTHENTICATION_EXCEPTION = ...;
public const VALIDATION_EXCEPTION = ...;
```

 then edit `config/response_builder.php` file to map exceptions to your codes:

```php
	'exception_handler' => [
		'exception' => [
			'http_not_found'           => ['code' => ApiCode::HTTP_NOT_FOUND],
			'http_service_unavailable' => ['code' => ApiCode::HTTP_SERVICE_UNAVAILABLE],
			'http_exception'           => ['code' => ApiCode::HTTP_EXCEPTION],
			'uncaught_exception'       => ['code' => ApiCode::UNCAUGHT_EXCEPTION],
			'authentication_exception' => ['code' => ApiCode::AUTHENTICATION_EXCEPTION],
			'validation_exception'     => ['code' => ApiCode::VALIDATION_EXCEPTION],
		],
    ],
```

## HTTP return codes ##

 You can also configure HTTP return code to use with each exception, by using `http_code` key
 for each of exceptions you need.

 > ![NOTE](img/notes.png) You must use valid HTTP error code. Codes outside of range from `400` 
 > (`BaseApiCodes::ERROR_HTTP_CODE_MIN`) to `599` (`BaseApiCodes::ERROR_HTTP_CODE_MAX`) will be ignored
 > and default value will be used instead.

 I.e. to alter HTTP code for `http_not_found`:
 
```php
'http_not_found' => [
    'code'      => BaseApiCodes::EX_HTTP_NOT_FOUND(),
    'http_code' => HttpResponse::HTTP_BAD_REQUEST,
],
```

 See default config `vendor/marcin-orlowski/laravel-api-response-builder/config/response_builder.php`
 file for all entries you can modify.

 Both keys `code` and `http_code` are optional and can be used selectively according to your needs.
 Helper will fall back to defaults if these are not found:

```php
'http_not_found' => [
    'http_code' => HttpResponse::HTTP_BAD_REQUEST,
],
'http_service_unavailable' => [
    'code' => BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE(),
],
'uncaught_exception' => [
],
````

 Helper will try to use exception's status code if no dedicated `http_code` value is provided but it will fall
 to default `HttpResponse::HTTP_BAD_REQUEST` code (`HttpResponse::HTTP_INTERNAL_SERVER_ERROR` for uncaught
 exceptions) if exceptions status code is `0` (zero).

## Error messages ##

 If you want to override built-in messages for any (or all) exceptions, edit `config/response_builder.php`
 and add appropriate entry to `map` array:

```php
'map' => [
    BaseApiCodes::EX_HTTP_NOT_FOUND()           => 'api.http_not_found',
    BaseApiCodes::EX_HTTP_SERVICE_UNAVAILABLE() => 'api.http_service_unavailable',
    BaseApiCodes::EX_HTTP_EXCEPTION()           => 'api.http_exception',
    BaseApiCodes::EX_UNCAUGHT_EXCEPTION()       => 'api.uncaught_exception',
    BaseApiCodes::EX_AUTHENTICATION_EXCEPTION() => 'api.authentication_exception',
    BaseApiCodes::EX_VALIDATION_EXCEPTION()     => 'api.validation_exception',
    ...
],
```

 where `api.xxxx` entry must be valid localization string key from your app's localization strings
 pool as per Lang's requirements. You can use placeholders in your messages. Supported are 
 `:api_code` being substituted by actual code assigned to this exception and `:message`
 replaced by exception's `getMessage()` return value.

## Important notes ##

### Possible Exception Handler conflicts ###

 Please note that some 3rd party packages may also provide own exception handling helpers and may 
 recommend using said handlers in your application. Unfortunately this will cause conflict with
 `ResponseBuilder`'s handler which usually lead to one (or another) handler not being executed
 at all.

 For example if your API delegates OAuth2 related tasks to popular
 [lucadegasperi/oauth2-server-laravel](https://packagist.org/packages/lucadegasperi/oauth2-server-laravel) package, then you
 must **NOT** use its `OAuthExceptionHandlerMiddleware` class and ensure it is not set, by inspecting `app/Kernel.php` file
 and ensuring the following line (if present) is removed or commented out:

```php
// remove or comment out
'LucaDegasperi\OAuth2Server\Middleware\OAuthExceptionHandlerMiddleware',
```

### ApiCodes ###

 The above assumes you keep your codes in `ApiCodes` class stored in `app/ApiCodes.php` and using `App\` namespace.
