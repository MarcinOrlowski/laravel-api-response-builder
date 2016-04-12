# API Response Builder for Laravel 5 #
 
Response Builder is Laravel5's helper designed to simplify building 
nice, normalized and easy to consume REST API responses.

 
## Response structure ##

For simplicity of consuming, produced JSON response is **always** the same at its corem and contains the following data:

  * `success` (boolean) determines if API method succeeded or not
  * `code` (int) being your own return code
  * `locale` (string) locale used for error message (obtained automatically via \App::getLocale())
  * `message` (string) human readable description of `code`
  * `data` (object|null) your returned payload or `null` if there's no data to return.


## Return Codes ##

Return codes must be positive integer. Code `0` (zero) always means success, all
other codes are treated as error codes.
 

#### Code Ranges ####

In one of our projects we had multiple APIs chained together (so one API
called another). In case of method failure we wanted to be able to 
do the "cascade" and use return code provided by API that failed. For
example our API consumer call method of publicly exposed API "A". That
API uses internal API "B" method, but under the hood "B" also delegates 
some work and talks to API "C". In case of failure of method in "C",
API consumer would see its' return code. This simplifies the code, and
helps keep features separated but to make this work you must ensure no
API return code overlaps, otherwise you cannot easily tell which one in
your chain failed. For that reason Response Builder supports code ranges,
allowing you to configure `min_code` and `max_code` you want to be allowed,
and no code outside this range would be allowed by Response Builder.
 
**NOTE:** codes from `0` to `63` (inclusive) are reserved by Response Builder and
cannot be used by your codes.


## Exposed Methods ##

All ResponseBuilder methods are **static**, and for simplicity of use, it's recommended to 
add the following `use` to code that calls Response Builder methods:
 
    use MarcinOrlowski\ResponseBuilder\ResponseBuilder;


Methods' arguments:

 * `$data` (array|null) data you want to be returned in response's `data` node,
 * `$http_code` (int) valid HTTP return code (see HttpResponse class for useful constants),
 * `$lang_args` (array) array of arguments passed to `Lang::get()` while building `message`,
 * `$error_code` (int) error code you want to be returned in `code`,
 * `$message` (string) custom message to be returned as part of error response.
 
Most arguments of `success()` and `error()` methods are optional, with exception for `$error_code`
for the latter. Helper methods arguments are partially optional - see signatures below for details.

**IMPORTANT:** If you want to use own `$http_code`, ensure it is right for the purpose.
Response Builder will throw `\InvalidArgumentException` if you use `$http_code` outside
of 200-299 range with `success()` and related methods and it will also do the same
for `error()` and related methods if `$http_code` will be lower than 400.
 
Redirection codes 3xx cannot be used with Response Builder.
 
See [W3 specs page](https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html) for more details on HTTP codes.

#### Reporting Sucess ####

    success(array $data = null, $http_code = HttpResponse::HTTP_OK, array $lang_args = []);
    successWithHttpCode($http_code);
    
Usage restrictions:

* `$http_code` must be in range from 200 to 299

#### Reporting Error ####

    error($error_code, $lang_args = [], $data = null, $http_code = HttpResponse::HTTP_BAD_REQUEST);
    errorWithData($error_code, $data, array $lang_args = []);
    errorWithDataAndHttpCode($error_code, $data, $http_code, array $lang_args = []);
    errorWithHttpCode($error_code, $http_code, $lang_args = []);
    errorWithMessage($error_code, $error_message, $http_code = HttpResponse::HTTP_BAD_REQUEST);

Usage restrictions:

* `$error_code` must not be 0
* `$http_code` must not be lower than 400


## Usage examples ##

#### Success ####

To report success from your Controller just do:
 
    return ResponseBuilder::success();
 
which will produce:
 
     {
       "success": true,
       "code": 0,
       "locale": "en",
       "message": "OK",
       "data": null
     }

If you want to return some data back:

    $data = [ "foo" => "bar" ];
    return ResponseBuilder::success($data);

which would produce:

    {
      ...
      "data": {
         "foo": "bar"
      }
    }

**IMPORTANT:** `data` node is **always** returned as JSON Object. This is enforced by design, to simplify
response consumption, and simplifying further backward compatible changes like adding new fields to 
returned data. Therefore passing array directly would produce unwanted results:
 
    $method_response = [1,2,3];
    return ResponseBuilder::success($method_response);
    
which would produce:

    {
      ...
      "data": {
         "0": 1,
         "1": 2,
         "2": 3
      }
    }
    
The `0`, `1`, `2` keys come from array index. The proper way of returning array (i.e. list things), 
it to simply wrap it in another array:

    $method_response = [1,2,3];
    $data = ['things' => method_response];
    return ResponseBuilder::success($data);
    
which would produce expected and much cleaner data structure:

    {
      ...
      "data": {
         "things": [1, 2, 3]
      }
    }


#### Errors ####

Returning error is almost as simple as returning success, however you need to provide at least error 
code. I strongly suggest not to use numeric values directly in your code but to create separate class,
keep all used codes there and reference them:
  
    class ErrorCode {
        const SOMETHING_WENT_WRONG = 250;
    }

To report failure of your method just do:

    return ResponseBuilder::error(ErrorCodes::SOMETHING_WENT_WRONG);
    
which would produce:

    {
      "success": false,
      "code": 250,
      "locale": "en",
      "message": "Error #250 occurred.",
      "data": null
    }

As there's no custom message, `message` field returns built-in message. To provide custom message you 
need to edit add  entry for `ErrorCodes::SOMETHING_WENT_WRONG` to `map` array in Response Builder 
configuration file. See `Response Builder Configuration` section for details

To report failure with error code mapped to message using placeholders:

    return ResponseBuilder::error(ErrorCodes::SOMETHING_WENT_WRONG, ['login' => $login]);
    
You can override message mapping by providing error message by hand by using `errorWithMessage()` 
but this expects final string provided, so if you need substitution, you need to resolve
them in your code:

    $msg = Lang::get('message.something_wrong', ['login' => $login]);
    return ResponseBuilder::errorWithMessage(ErrorCodes::SOMETHING_WENT_WRONG, $msg);


## Installation ##

To install Response Builder all you need to do is to open your shell and do:

    composer require marcin-orlowski/laravel-api-response-builder

then publish default configuration file to `config/` folder of your app:
 
    php artisan vendor:publish


## Laravel setup ##

Edit `app/config.php` and add the following line to your `providers` array:

    MarcinOrlowski\ResponseBuilder\ResponseBuilderServiceProvider::class,
      

## Response Builder Configuration ##

Response Builder configuration can be found in `config/response_builder.php` file. Supported
configuration keys (all must be present):

 * `min_code` (int) lowest allowed code for assigned code range (inclusive)
 * `max_code` (int) highest allowed code for assigned code range (inclusive)
 * `map` (array) maps error codes to localization string keys.
  

Maping example:
 
    'map' => [
        ErrorCodes::SOMETHING => 'api.something',
    }
    
If you do not have dedicated error message, use `null` instead:

    'map' => [
        ErrorCodes::SOMETHING => null,
    }

If you do not want to use code ranges in your API, just set `max_code` in
configuration file to some very high value.

**NOTE:** if given error code is not present in `map`, Response Builder will provide 
default message automatically. If you want to override this message, see `Overriding
built-in messages` section.


## Messages and Localization ##

Response Builder is designed with localization in mind so default approach is you just set it up
once and most is done automatically. This also includes creating human readable error messages.
As described in `Configuration` section, once you got `map` populated, you most likely will not
be in need to manually refer error messages - Response Builder will do that for you and you optionally
just need to pass array with placeholders' substitution (hence the order of arguments for `errorXXX()` 
methods). Response Builder utilised standard Laravel's Lang class to deal with messages, so all features
localization are supported.


## Handling exceptions the right way ##

Properly designed API shall never hit consumer with HTML nor anything like that. While in regular use this
is quite easy to achieve, unexpected problems like uncaught exception or even enabled maintenance mode
can confuse many APIs world wide. Do not be one of them and take care of that too. With Laravel this
can be achieved with custom Exception Handler and Response Builder comes with ready-to-use recipe in
[extras/](extras/) folder.


## Overriding built-in messages ##

At the moment Response Builder provides few built-in messages (see [src/ErrorCodes.php](src/ErrorCodes.php)):
one is used for success code `0` and another serves as fallback message for codes without mapping. If for any
reason you want to override them simply map these codes in your `map` config:

     MarcinOrlowski\ResponseBuilder\ErrorCodes::OK => 'my_messages.ok',

and from now on, each `success()` will be returning mapped message.

To override default error message used when given error code has no entry in `map`, add the following:

     MarcinOrlowski\ResponseBuilder\ErrorCodes::NO_ERROR_MESSAGE => 'my_messages.default_error_message_fmt',

You can use `:error_code` placeholder in the message and it will be substituted actual error code value.
 

## Notes ##

* Response Builder is **not** compatible with Lumen framework, mainly due to lack of Lang class.
* Tests will be released shortly. They do already exist, however Response Builder was extracted from existing project and require some work to remove dependencies. 
