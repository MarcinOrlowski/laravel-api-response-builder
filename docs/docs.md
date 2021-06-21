![REST API Response Builder for Laravel](../artwork/laravel-api-response-builder-logo.svg)

## Fundamentals ##

[« Documentation table of contents](README.md)

 * [Structure of JSON response](#response-structure)
 * [Return Codes and Code Ranges](#return-codes)
   * [Code ranges](#code-ranges)
   * [ApiCodes class](#apicodes-class)
 * [Messages and Localization](#messages-and-localization)
 * [Handling Exceptions API way](#handling-exceptions-api-way)
 * [Overriding built-in messages](#overriding-built-in-messages)
 
---
 
# Response structure #

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
  * `data` (**object**|**null**) if you return any additional data with your reply, it would end here.
    If no extra data is needed, that key still be present in the response with `null` value.

 **NOTE:** If you need to return other/different elements in the above structure (not in your `data`),
 see [Manipulating Response Object](response.md) chapter for detailed information about how
 to achieve this.

# Return Codes #

 All return codes are integers however the meaning of the code is fully up to you. The only exception
 is `0` (zero) which **ALWAYS** means **success** and you cannot use `0` with `error()` methods (but
 you can have other codes for success than `0` if needed).

## Code Ranges ##

 In one of my projects we had multiple APIs chained together (so one API called another, remote API). I wanted to be able to chain
 API invocations in the way that in case of problems (and cascading failure) I still would able to tell which one failed first.
 For example our API client app calls method of publicly exposed API "A". That API "A" internally calls method of completely
 different and separate API "B". Under the hood API "B" delegates some work and talks to API "C". When something go wrong and
 "C"'s method fail, client shall see "C"'s error code and error message, not the "A"'s. To achieve this each API you chain return
 unique error codes and the values are unique per whole chain To support that `ResponseBuilder` features code ranges, allowing
 you to configure `min_code` and `max_code` you want to be allowed to use in given API. `ResponseBuilder` will ensure no values not
 from that range is ever returned, so to make the whole chain "clear", you only need to properly assign non-overlapping ranges to
 your APIs and `ResponseBuilder` do the rest. Any attempt to violate code range ends up with exception thrown.

 > ![IMPORTANT](img/warning.png) First `20` codes in your range (from `0` to `19` inclusive) are reserved for `ResponseBuilder`
 > internals and must not be used directly nor assigned to your codes.

 > ![NOTE](img/notes.png) Code ranges cannot be turned off, but if you do not need it or you just have one API or need
 > no chaining, then just set `max_code` in your configuration file to some very high value if needed or defaults do not fit.


## ApiCodes class ##

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

# Messages and Localization #

 `ResponseBuilder` is designed with localization in mind so default approach is you just set it up
 once and most things should happen automatically, which also includes creating human readable error messages.
 As described in `Configuration` section, once you get `map` configured, you most likely will not
 be in need to manually refer error messages - `ResponseBuilder` will do that for you and you optionally
 just need to pass array with placeholders' substitution (hence the order of arguments for `errorXXX()`
 methods). `ResponseBuilder` utilised standard Laravel's `Lang` class to deal with messages, so all
 localization features are supported.

# Handling Exceptions API way #

 Properly designed REST API should never hit consumer with anything but JSON. While it looks like easy task,
 there's always chance for unexpected issue to occur. So we need to expect unexpected and be prepared when
 it hit the fan. This means not only things like uncaught exception but also Laravel's maintenance mode can
 pollute returned API responses which is unfortunately pretty common among badly written APIs. Do not be
 one of them, and take care of that in advance with couple of easy steps.
 With Laravel this can be achieved with custom Exception Handler and `ResponseBuilder` comes with ready-to-use
 Handler as well. See [Exception Handling with Response Builder](exceptions.md) for easy setup information.

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

# Overriding built-in messages #

 At the moment `ResponseBuilder` provides few built-in messages (see [src/BaseApiCodes.php](../src/BaseApiCodes.php)):
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

