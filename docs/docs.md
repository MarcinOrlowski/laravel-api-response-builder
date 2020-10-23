![REST API Response Builder for Laravel](img/logo.png)

# REST API Response Builder for Laravel #

 `ResponseBuilder` is [Laravel](https://laravel.com/)'s helper designed to build
 nice, normalized and easy to consume REST API JSON responses.

## Table of contents ##

 * [Response structure](#response-structure)
 * [Usage examples](examples.md)
 * [Return Codes and Code Ranges](#return-codes)
 * [Exposed Methods](#exposed-methods)
 * [Data Conversion](conversion.md)
 * [Requirements](installation.md#requirements)
 * [Installation and Configuration](installation.md)
 * [Handling Exceptions API way](#handling-exceptions-api-way)
 * [Manipulating Response Object](#manipulating-response-object)
 * [Overriding built-in messages](#overriding-built-in-messages)
 * [Unit testing your ApiCodes](testing.md)
 * [License](#license)
 * [Notes](#notes)

----

## Response structure ##

 Predictability, simplicity and no special-case is the key of the `ResponseBuilder` and all responses created by
 this library **guarantee** consistent JSON structure by design.

 By default response always contain at least the following elements:

```json
{
  "success": true,
  "code": 0,
  "locale": "en",
  "message": "OK",
  "data": null
}
```

 where

  * `success` (**boolean**) indicates API method failure or success,
  * `code` (**int**) is your own return code (usually used when returning error message or other failure),
  * `locale` (**string**) represents locale used for returned error message (obtained automatically via
    `\App::getLocale()`). This helps processing the response if you support multiple languages,
  * `message` (**string**) human readable message that is ready to display and explains human readable explanation
    of the `code` value,
  * `data` (**object**|**array**|**null**) if you return any additional data with your reply, it would end here.
    If no extra data is needed, that key still be present in the response with `null` value.

 **NOTE:** If you need to return other/different elements in the above structure (not in your `data`),
 see [Manipulating Response Object](#manipulating-response-object) chapter for detailed information about how
 to achieve this.

----

## Return Codes ##

 All return codes are integers however the meaning of the code is fully up to you. The only exception
 is `0` (zero) which **ALWAYS** means **success** and you cannot use `0` with `error()` methods (but
 you can have other codes for success than `0` if needed).

#### Code Ranges ####

 In one of my projects we had multiple APIs chained together (so one API called another, remote API). I wanted to be able to chain
 API invocations in the way that in case of problems (and cascading failure) I still would able to tell which one failed first.
 For example our API client app calls method of publicly exposed API "A". That API "A" internally calls method of completely
 different and separate API "B". Under the hood API "B" delegates some work and talks to API "C". When something go wrong and
 "C"'s method fail, client shall see "C"'s error code and error message, not the "A"'s. To achieve this each API you chain return
 unique error codes and the values are unique per whole chain To support that `ResponseBuilder` features code ranges, allowing
 you to configure `min_code` and `max_code` you want to be allowed to use in given API. `ResponseBuilder` will ensure no values not
 from that range is ever returned, so to make the whole chain "clear", you only need to properly assign non-overlapping ranges to
 your APIs and `ResponseBuilder` do the rest. Any attempt to violate code range ends up with exception thrown.

 **IMPORTANT:** first `20` codes in your range (from `0` to `19` inclusive) are reserved for `ResponseBuilder` internals and
 must not be used directly nor assigned to your codes.

 **NOTE:** code ranges cannot be turned off, but if you do not need it or you just have one API or need no chaining, then just
 set `max_code` in your configuration file to some very high value if needed or defaults do not fit.

----

## Exposed Methods ##

 Starting from version 6.4, `ResponseBuilder` uses new API implementation that uses
 [Builder pattern](https://en.wikipedia.org/wiki/Builder_pattern), which is far more flexible that previous bag of methods.

### Helpers ###

 But while Builder is pretty proverful interface, if you need to return just success or error, without too many additional data
 attached, using it may look like overkill, therefore there are two helper methods (from old API) that serve as shortcuts
 for reporting success or failure:

 * `success($data, $api_code, $placeholders, $http_code, $json_opts)`

    Returns success indicating message. You can ommit `$api_code` to fall back to default code for `OK`). All params are
    optional. Usage example:

    ```php
    return RB::success();
    ```

  * `public static function error(int $api_code, array $placeholders = null, $data = null, int $http_code = null, int $json_opts = null)`

    Returns error indicating response. `$api_code` must not equal to value indicating `OK` (`ApiCodes::OK()`), all other params
    are optional.

    ```php
    return RB::error(ApiCodes::SOMETHING_FAILED);
    ```

### Builder ###

 There are two static methods that return instance of the Builder: `asSuccess()` and `asError()`. For example, the following
 code would return response indicating a success, with additional data and custom HTTP code:

```php
return RB::asSuccess()
      ->withData($data)
      ->withHttpCode(HttpResponse::HTTP_CREATED)
      ->build();
```

 Naturally, if you just need to return success without any payload, just call `success()` as you would have in previous
 versions:

```php
return RB::success();
```

 Builder static methods:

 * `asSuccess($api_code)`: Returns Builder instance configured to return success indicating message.
   You can ommit `$api_code` to fall back to default code for `OK` (`ApiCodes::OK()`).
 * `asError($api_code)`: Returns Builder instance configured to produce error indicating response. `$api_code`
   must not equal to value indicating `OK` (`ApiCodes::OK()`).

 In both cases `api_code` (**int**) is any integer value you want to be returned as `code` in final response.

 Parameter setters:

 * `withHttpCode($code)`: (**int**) valid HTTP return code (see `HttpResponse` class for useful constants). For
   `success()` responses, `$http_code` must be in range from 200 to 299 (inclusive), while for `error()` it must be in
   range from 400 to 599 (inclusive) otherwise `\InvalidArgumentException` will be thrown. HTTP codes from 3xx pool
   (redirection) are not allowed. Please see [W3 specification](https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html)
   for more information about all HTTP codes and their meaning.
 * `withData($data)`: (**object**|**array**|**null**) data you want to be returned in your response in `data` node,
 * `withJsonOptions($opts)`: (**int**) data-to-json conversion options as
   [documented](http://php.net/manual/en/function.json-encode.php). Pass `null` for
   default `RB::DEFAULT_ENCODING_OPTIONS` ([source](../src/ResponseBuilder.php)). Please see
   [configuration](../config/response_builder.php) file and config's `encoding_options` too.
 * `withMessage($message)`: (**string**) custom message to be returned as part of error response
   (avoid, use error code mapping feature).
 * `withPlaceholders($placeholders)`: (**array**) array of placeholders as expected by `Lang::get()` while building
   response `message` based on localization files (as configured in i.e. `map`) or strings with placeholders.
 * `withHttpHeaders($headers)`

 Once all the arguments are passed, call `build()` to have final `HttpResponse` object returned.

 **IMPORTANT:** To enforce constant JSON structure of the response, `data` node is always an JSON object, therefore passing
 anything but `object` or `array` to `withData()` would trigger internal type casting. There's no smart logic here, just
 ordinary `$data = (object)$data;`. The only exception are classes configured with "classes" mapping (see configuration
 details). In such case configured conversion method is called on the provided object and result is returned instead.
 Several classes pre-configured but you can add additional classes just by creating entry in configuration `converter` mapping.
 See [Data Conversion](conversion.md) for more information.

----

#### ApiCodes class ####

 To keep your source readable and clear, it's strongly recommended to create separate class
 `ApiCode.php` (i.e. in `app/`) and keep all codes there as `public const`. This way you protect
 yourself from using wrong code or save your time in case you will need to refactor code
 range in future. For example, your imaginary `app/ApiCode.php` can look like this:

```php
<?php

namespace App;

class ApiCode {
   public const SOMETHING_WENT_WRONG = 250;
}
```

----

## Messages and Localization ##

 `ResponseBuilder` is designed with localization in mind so default approach is you just set it up
 once and most things should happen automatically, which also includes creating human readable error messages.
 As described in `Configuration` section, once you get `map` configured, you most likely will not
 be in need to manually refer error messages - `ResponseBuilder` will do that for you and you optionally
 just need to pass array with placeholders' substitution (hence the order of arguments for `errorXXX()`
 methods). `ResponseBuilder` utilised standard Laravel's `Lang` class to deal with messages, so all
 localization features are supported.

----

## Handling Exceptions API way ##

 Properly designed REST API should never hit consumer with anything but JSON. While it looks like easy task,
 there's always chance for unexpected issue to occur. So we need to expect unexpected and be prepared when
 it hit the fan. This means not only things like uncaught exception but also Laravel's maintenance mode can
 pollute returned API responses which is unfortunately pretty common among badly written APIs. Do not be
 one of them, and take care of that in advance with couple of easy steps.
 With Laravel this can be achieved with custom Exception Handler and `ResponseBuilder` comes with ready-to-use
 Handler as well. See [Exception Handling with Response Builder](exceptions.md) for easy setup information.

----

## Manipulating Response Object ##

 If you need to return more fields in response object you can simply extend `ResponseBuilder` class
 and override `buildResponse()` method.

### Custom response structure ###

 For example, you want to get rid of `locale` field and add server time and timezone to returned
 responses. First, create `MyResponseBuilder.php` file in `app/` folder (both location and class
 name can be anything you wish, just remember to adjust the namespace too) and override
 `buildResponse()` method which builds normalized response array for all the helper methods.
 So the class content should be as follow:

```php
<?php

namespace App;

class MyResponseBuilder extends MarcinOrlowski\ResponseBuilder\ResponseBuilder
{
   protected static function buildResponse(bool $success, int $api_code, 
                                           $message_or_api_code, array $lang_args = null,
                                           $data = null, array $debug_data = null): array
   {
      // tell ResponseBuilder to do all the heavy lifting first
      $response = parent::buildResponse($success, $api_code, $message_or_api_code, $lang_args, $data, $debug_data);

      // then do all the tweaks you need
      $date = new DateTime();
      $response['timestamp'] = $date->getTimestamp();
      $response['timezone'] = $date->getTimezone();

      unset($response['locale']);

      // finally, return what $response holds
      return $response;
   }

}
```

 and from now on use `MyResponseBuilder` class instead of `ResponseBuilder`. As all responses are
 always produced with use of `buildResponse()` internally, your **all** responses will be affected
 the same way. For example:

```php
MyRB::success();
```

 which should then return your desired JSON structure:

```json
{
  "success": true,
  "code": 0,
  "message": "OK",
  "timestamp": 1272509157,
  "timezone": "UTC",
  "data": null
}
```

 and

```php
$data = [ 'foo'=>'bar ];
return MyRB::errorWithData(ApiCode::SOMETHING_WENT_WRONG, $data);
```

 would produce:

```json
{
   "success": false,
   "code": 250,
   "message": "Error #250",
   "timestamp": 1272509157,
   "timezone": "UTC",
   "data": {
      "foo": "bar"
   }
}
```

### Overriding code to message conversion ###

 `ResponseBuilder` automatically provides human readable error messages for each API code used but if for any
 reason you want to take control on this, you can now provide own implementation of `RB::getMessageForApiCode()`.

```php
<?php

namespace App;

class MyResponseBuilder extends MarcinOrlowski\ResponseBuilder\ResponseBuilder
{
   protected static function getMessageForApiCode(bool $success, int $api_code, array $lang_args = null): string
   {
       return "My cool message for code {$api_code}";
   }
}
```

 Please see current implementation for `getMessageForApiCode()` for details how to correctly obtain localization string key etc.

----

## Overriding built-in messages ##

 At the moment `ResponseBuilder` provides few built-in messages (see [src/ErrorCode.php](src/ErrorCode.php)):
 one is used for success code `0` and another provides fallback message for codes without custom mapping. If for
 any reason you want to override them, simply map these codes in your `map` config using codes from package
 reserved range:

```php
MarcinOrlowski\ResponseBuilder\BaseApiCodes::OK() => 'my_messages.ok',
```

 and from now on, each `success()` will be returning your message instead of built-in one.

 To override default error message used when given error code has no entry in `map`, add the following:

```php
MarcinOrlowski\ResponseBuilder\BaseApiCodes::NO_ERROR_MESSAGE() => 'my_messages.default_error_message',
````

 You can use `:api_code` placeholder in the message and it will be substituted actual error code value.

