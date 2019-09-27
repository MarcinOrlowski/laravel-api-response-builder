![REST API Response Builder for Laravel](img/logo.png)

# REST API Response Builder for Laravel #

 `ResponseBuilder` is [Laravel](https://laravel.com/)'s helper designed to simplify building
 nice, normalized and easy to consume REST API responses.

## Table of contents ##

 * [Response structure](#response-structure)
 * [Usage examples](#usage-examples)
 * [Return Codes and Code Ranges](#return-codes)
 * [Exposed Methods](#exposed-methods)
 * [Data Conversion](#data-conversion)
 * [Requirements](#requirements)
 * [Installation and Configuration](#installation-and-configuration)
 * [Handling Exceptions API way](#handling-exceptions-api-way)
 * [Manipulating Response Object](#manipulating-response-object)
 * [Overriding built-in messages](#overriding-built-in-messages)
 * [Unit testing your ApiCodes](testing.md)
 * [License](#license)
 * [Notes](#notes)

----

## Response structure ##

 Predictability, simplicity and no special-case is the key of the `ResponseBuilder` design. I wanted to make my life easier not
 only when I develop the API itself, but also when I'd try to use it i.e. in mobile applicationsm, therefore response created with
 this package **guarantees** consisten JSON structure.
 
 Default response will always contains at least the following elements:

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
  * `locale` (**string**) represents locale used for returned error message (obtained automatically via `\App::getLocale()`). This helps processing the response if you support multiple languages,
  * `message` (**string**) human readable message that is ready to display and explains human readable explanation of the `code` value,
  * `data` (**object**|**array**|**null**) if you return any additional data with your reply, it would end here. If no extra data is needed, that key still be present in the response with `null` value.

 **NOTE:** If you need to return other/different elements in the aboive structure (not in your `data`), see [Manipulating Response Object](#manipulating-response-object) chapter for detailed information about how to achieve this.

----

## Usage examples ##

 The following examples assume `ResponseBuilder` is properly installed and available to your Laravel application. Installation
 steps are described in details in further chapters, if help is needed.

#### Success ####

 To report response indicating i.e. operation success, simply your Controller method with:

    return ResponseBuilder::success();

 which will produce and return the following JSON object:
 
```json
{
  "success": true,
  "code": 0,
  "locale": "en",
  "message": "OK",
  "data": null
}
```

 If you would like to return some data with it (which pretty much always the case :), pass it to `success()` as argument:

```php
$data = [ 'foo' => 'bar' ];
return ResponseBuilder::success($data);
```

 which would return:

```json
{
  "success": true,
  "code": 0,
  "locale": "en",
  "message": "OK",
  "data": {
      "foo": "bar"
  }
}
```

 **NOTE:** As all the data in the response structure must be represented in JSON, `ResponseBuilder` only accepts certain types of
 data - you can either pass an `array` or object of any class that can be converted to valid JSON (i.e. Eloquent's Model or
 Collection). Data conversion goes on-the-fly, if you need any additional classes supported than said Model or Collection (which
 are pre-configured), you need to instruct `ResponseBuilder` how to deal with it. See [Data Conversion](#data-conversion) chapter
 for more details. Attempt to pass unsupported data type (i.e. literals) will throw the exception.  

 **IMPORTANT:** `data` node is **always** an JSON Object. This is **enforced** by the library design, therefore if you need to
 return your data array as array and just its elements as shown in above example, you must wrap it in another array:

```php
// this is CORRECT
$returned_array = [1,2,3];
$data = ['my_array' => $returned_array];
return ResponseBuilder::success($data);
```

 which would give:

```json
{
   ...
   "data": {
      "my_array": [1, 2, 3]
   }
}
```

 **IMPORTANT:** do NOT wrap the payload into array without giving it the key would, due to conversion to JSON object: 

```php
// this is WRONG
$returned_array = [1,2,3];
return ResponseBuilder::success($returned_array);
```

would give you wrong `data` structure: 

```json
{
  ...
  "data": {
     "0": 1,
     "1": 2,
     "2": 3
  }
}
```

 which most likely is not what your client expects. Note that you must also not use this as side effect, because created
 keys are based on array internals:

```php
// this is WRONG
$returned_array = [1,2,3];
unset($returned_array[1]);
return ResponseBuilder::success($returned_array);
```
 
 would give non-sequential keys:
 
```json
{
  ...
  "data": {
     "0": 1,
     "2": 3
  }
}
```

#### Errors ####

 Returning error responses is also simple, however in such case you are required to need to additionally pass at least your own
 error code to `error()` to tell the client what the error it is:

```php
    return ResponseBuilder::error(<CODE>);
```

 To make your life easier (and your code [automatically testable](testing.md)) you should put all error codes you use
 in separate `ApiCodes` class, as its `public const`s, which would improve code readability and would prevent certain
 types of coding error from happening. Please see [Installation and Configuration](#installation-and-configuration) 
 for details.
 
 Example usage:

```php
    return ResponseBuilder::error(ApiCode::SOMETHING_WENT_WRONG);
```
 
 which would produce the following JSON response:

```json
{
   "success": false,
   "code": 250,
   "locale": "en",
   "message": "Error #250",
   "data": null
}
```

 Please see the value of `message` element above. `ResponseBuilder` tries to automatically obtain text error message associated 
 with the error code used. If there's no message associated, it will fall back to default, generic error "Error #xxx", as shown 
 above. Such association needs to be configured in `config/response_builder.php` file, using `map` array, so see 
 [ResponseBuilder Configuration](#response-builder-configuration) for more information. 

 As `ResponseBuilder` uses Laravel's `Lang` package for localisation, you can use the same features with your messages as you use
 across the whole application, including message placeholders:

    return ResponseBuilder::error(ApiCodeBase::SOMETHING_WENT_WRONG, ['login' => $login]);

 and if message assigned to `SOMETHING_WENT_WRONG` code uses `:login` placeholder, it will be correctly replaced with content of
 your `$login` variable.

 You can, however this is not recommended, override built-in error message mapping too as `ResponseBuilder` comes with
 `errorWithMessage()` method, which expects string message as argument. This means you can just pass any string you want and 
 it will be returned as `message` element in JSON response regardless the `code` value. Please note this method is pretty 
 low-level and string is used as is without any further processing. If you want to use `Lang`'s placeholders here, you need
 to handle them yourself by calling `Lang::get()` manually first and pass the result:

```php
$msg = Lang::get('message.something_wrong', ['login' => $login]);
return ResponseBuilder::errorWithMessage(ApiCodeBase::SOMETHING_WENT_WRONG, $msg);
```

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
 "C"'s metod fail, client shall see "C"'s error code and error message, not the "A"'s. To acheive this each API you chain return
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

 All `ResponseBuilder` methods are **static**, and for simplicity of use, it's recommended to add the following `use` to your code:

    use MarcinOrlowski\ResponseBuilder\ResponseBuilder;


 Methods' arguments:

 * `$api_code` (**int**) any integer value you want to be returned in `code`,
 * `$data` (**mixed**|**null**) any data you want to be returned in your response as `data` node,
 * `$http_code` (**int**) valid HTTP return code (see `HttpResponse` class for useful constants),
 * `$lang_args` (**array**) array of arguments passed to `Lang::get()` while building `message`,
 * `$message` (**string**) custom message to be returned as part of error response (avoid, use error code mapping feature).
 * `$encoding_options` (**int**) data-to-json conversion options as [described in documentation of json_encode()](http://php.net/manual/en/function.json-encode.php). Pass `null` for default `ResponseBuilder::DEFAULT_ENCODING_OPTIONS` ([source](https://github.com/MarcinOrlowski/laravel-api-response-builder/blob/master/src/ResponseBuilder.php#L47)). See [configuration](https://github.com/MarcinOrlowski/laravel-api-response-builder/blob/master/config/response_builder.php#L106) (see config's `encoding_options` too)

 Most arguments of `success()` and `error()` are optional, with exception for `$api_code`
 for the `error()` and related methods. Helper methods arguments are partially optional - see
 signatures below for details.

 **NOTE:** `$data` can be of any type you want (i.e. `string`) however, to enforce constant JSON structure
 of the response, `data` is always an object. If you pass anything else, type casting will be done internally.
 There's no smart logic here, just ordinary `$data = (object)$data;`. The only exception are classes configured
 with "classes" mapping (see configuration details). In such case configured conversion method is called on
 the provided object and result is returned instead. Laravel's `Model` and `Collection` classes are pre-configured
 but you can add additional classes just by creating entry in configuration `classes` mapping.

 I recommend you always pass `$data` as an `array` or object with conversion mapping configured, otherwise
 passing other types to `ResponseBuilder` may end up with response JSON featuring oddities like array keys
 keys named `0` or `scalar`.

 **IMPORTANT:** If you want to return own value of `$http_code` with the response data, ensure used
 value matches W3C meaning of the code. `ResponseBuilder` will throw `\InvalidArgumentException` if
 you try to call `success()` (and related methods) with `$http_code` not being in range of 200-299.
 The same will happen if you try to call `error()` (and family) with `$http_code` lower than 400.

 Other HTTP codes, like redirection (3xx) or (5xx) are not allowed and will throw `\InvalidArgumentException`.

 See [W3 specs page](https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html) for more details about
 available HTTP codes and its meaning.

#### Reporting Success ####

```php
success($data=null, $api_code=null, array $lang_args=[], $http_code=null, $encoding_options=null);
successWithCode($api_code=null, array $lang_args=[], $http_code=null);
successWithHttpCode($http_code);
```

 Usage restrictions:

 * `$http_code` must be in range from 200 to 299

#### Reporting Error ####

```php
error($api_code, $lang_args=[], $data=null, $http_code=HttpResponse::HTTP_BAD_REQUEST);
errorWithData($api_code, $data, array $lang_args=[], $encoding_options=null);
errorWithDataAndHttpCode($api_code, $data, $http_code, array $lang_args=[], $encoding_options=null);
errorWithHttpCode($api_code, $http_code, $lang_args=[]);
errorWithMessage($api_code, $error_message, $http_code=HttpResponse::HTTP_BAD_REQUEST);
```

 Usage restrictions:

 * `$api_code` must not be 0 (zero)
 * `$http_code` must not be lower than 400

----

## Data Conversion ##

 `ResponseBuilder` can save you some work by automatically converting certain objects
 prior returning response array. i.e. you can pass Eloquent's Model or Collection
 object directly and have it converted to array automatically.

 For example, passing `Model` object:

```php
$flight = App\Flight::where(...)->first();
return ResponseBuilder::success($flight);
```

 will return:

```json
{
   "item": {
      "airline": "lot",
      "flight_number": "lo123",
      ...
   }
}
```

 Or you have more data, the pass `Collection`:

```php
$flights = App\Flight::where(...)->get();
return ResponseBuilder::success($flights);
```

 which would return array of objects as expected:

```json
{
   "items": [
      {
         "airline": "lot",
         "flight_number": "lo123",
         ...
      },{
         "airline": "american",
         "flight_number": "am456",
         ...
      }
   ]
}
```

 The result is keyed `item` and `items`, depending on class mapping configuration (by default `Collection` is using `items` no matter we return one or even zero elements) is the given object of and the whole magic is done by calling method configured for given class.

 The whole functionality is configurable via `classes` mapping array:

```php
'classes' => [
     Illuminate\Database\Eloquent\Model::class => [
         'key'    => 'item',
         'method' => 'toArray',
     ],
     Illuminate\Database\Eloquent\Collection::class => [
         'key'    => 'items',
         'method' => 'toArray',
     ],
],
```

 The above confgures two classes (`Model` and `Collection`). Whenver object of that class is spotted, method specified in `method` key would be called on that obhject and data that method returns will be returned in JSON object using key specidied in `key`.

 So in above example, if we get `Collection`, then `ResponseBuilder` would call `toArray()` on it, and result data would
 be added in returned JSON in `items` object.

 When you pass the array it will be walked recursively and the conversion will take place
 on all known elements as well:

```php
$data = [
   'flight' = App\Flight::where(...)->first(),
   'planes' = App\Plane::where(...)->get(),
];
```

 would produce the following response (contrary to the previous examples, source array keys are preserved):

```json
{
   "flight": {
      "airline": "lot",
      "flight_number": "lo123",
      ...
   },
   "planes": [
      {
         "make": "airbus",
         "registration": "F-GUGJ",
         ...
      },{
         "make": "boeing",
         "registration": "VT-ANG",
         ...
      }
   ]
}
```

----

## Requirements ##

 Minimum requirements:

  * PHP 7.2+
  * Laravel 6.*

 The following PHP extensions are optional but strongly recommended:

   * iconv
   * mb_string

----

## Installation and Configuration ##

 To install `ResponseBuilder` all you need to do is to open your shell/cmd and do:

    composer require marcin-orlowski/laravel-api-response-builder

 If you want to use different configuration than `ResponseBuilder` defaults,
 publish and edit configuration file as described in [Configuration file](config.md)
 documentation.

#### Setup ####

 `ResponseBuilder` supports Laravel's auto-discovery feature and it's ready to use once
 installed with default configuration.

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


#### ResponseBuilder Configuration ####

 Package configuration can be found in `config/response_builder.php` file and
 each of its element is heavily documented in the file, so please take a moment
 and read it.

 Supported configuration keys (all keys **MUST** be present in config file):

 * `min_code` (int) lowest allowed code for assigned code range (inclusive)
 * `max_code` (int) highest allowed code for assigned code range (inclusive)
 * `map` (array) maps error codes to localization string keys.

 Code to message mapping example:

```php
'map' => [
    ApiCode::SOMETHING_WENT_WRONG => 'api.something_went_wrong',
],
```

 If given error code is not present in `map`, `ResponseBuilder` will provide fallback message automatically
 (default message is like "Error #xxx"). This means it's perfectly fine to have whole `map` array empty in
 your config, however you **MUST** have `map` key present nonetheless:

    'map' => [],

 Also, read [Overriding built-in messages](#overriding-built-in-messages) to see how to override built-in
 messages.

 **NOTE:** Config file may grow in future so if you are not using defaults, then on package upgrades
 check CHANGES.md to see if there're new configuration options. If so, and you already have config
 published, then you need to look into dist config file in `vendor/marcin-orlowski/laravel-api-response-builder/config/`
 folder and grab new version of config file.

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
 and override `buildResponse()` method:

    protected static function buildResponse($code, $message, $data = null);

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
   protected static function buildResponse($code, $message, $data = null)
   {
      // tell ResponseBuilder to do all the heavy lifting first
      $response = parent::buildResponse($code, $message, $data);

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
MyResponseBuilder::success();
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
return MyResponseBuilder::errorWithData(ApiCode::SOMETHING_WENT_WRONG, $data);
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

----

## Overriding built-in messages ##

 At the moment `ResponseBuilder` provides few built-in messages (see [src/ErrorCode.php](src/ErrorCode.php)):
 one is used for success code `0` and another provides fallback message for codes without custom mapping. If for
 any reason you want to override them, simply map these codes in your `map` config using codes from package
 reserved range:

```php
MarcinOrlowski\ResponseBuilder\ApiCodeBase::OK => 'my_messages.ok',
```

 and from now on, each `success()` will be returning your message instead of built-in one.

 To override default error message used when given error code has no entry in `map`, add the following:

```php
MarcinOrlowski\ResponseBuilder\ApiCodeBase::NO_ERROR_MESSAGE => 'my_messages.default_error_message',
````

 You can use `:api_code` placeholder in the message and it will be substituted actual error code value.

----

## License ##

 * Written and copyrighted &copy;2016-2019 by Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * ResponseBuilder is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
