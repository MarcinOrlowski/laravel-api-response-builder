# API Response Builder for Laravel 5 #

ResponseBuilder is Laravel5's helper designed to simplify building
nice, normalized and easy to consume REST API responses.


[![Latest Stable Version](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/v/stable)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)
[![Latest Unstable Version](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/v/unstable)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)
[![License](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/license)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)
[![Monthly Downloads](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/d/monthly)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)

## Table of contents ##
 
 * [Response structure](#response-structure)
 * [Return Codes and Code Ranges](#return-codes)
 * [Exposed Methods](#exposed-methods)
 * [Usage examples](#usage-examples)
 * [Installation and Configuration](#installation-and-configuration)
 * [Handling Exceptions API way](#handling-exceptions-api-way)
 * [Manipulate Response Object](#manipulate-response-object)
 * [Overriding built-in messages](#overriding-built-in-messages)
 * [Bugs reports and pull requests](#contributing)
 * [License](#license)
 * [Notes](#notes)


## Response structure ##

For simplicity of consuming, produced JSON response is **always** the same at its core and contains
the following data:

  * `success` (boolean) determines if API method succeeded or not,
  * `code` (int) being your own return code,
  * `locale` (string) locale used for error message (obtained automatically via `\App::getLocale()`),
  * `message` (string) human readable description, telling what `code` really means,
  * `data` (object|null) your returned payload or `null` if there's no data to return.

If you need to return other/different fields in response, see [Manipulating Response Object](#manipulating-response-object) 
chapter for detailed implementation guides.

## Return Codes ##

All return codes must be positive integer. Code `0` (zero) **ALWAYS** means **success**. All
other codes are considered error codes.


#### Code Ranges ####

In one of our projects we had multiple APIs chained together (so one API called another). So we wanted
to be able to chain API invocations and still be able to tell which one failed in case of problems.
For example our API consumer call method of publicly exposed API "A". That API uses internal API "B"
method, but under the hood "B" also delegates some work and talks to API "C". In case of failure of
method in "C", API consumer would see its' return code. This simplifies the code and helps keep features
separated but to make this work you must ensure no API return code overlaps, otherwise you cannot easily
tell which one in your chain failed. For that reason ResponseBuilder supports code ranges, allowing you
to configure `min_code` and `max_code` you want to be allowed in given API. No code outside this range would
be allowed by ResponseBuilder so once you assign non-overlapping ranges to your modules, your live
will be easier and ResponseBuilder will fail (throwing an exception) if wrong code is used, so your
unit tests should detect any error code clash easily.

If you do not need code ranges for your API, just set `max_code` in configuration file to some very high value.

**IMPORTANT:** codes from `0` to `63` (inclusive) are reserved by ResponseBuilder and cannot be assigned to your
codes.


## Exposed Methods ##

All ResponseBuilder methods are **static**, and for simplicity of use, it's recommended to
add the following `use` to make using ResponseBuilder easier:

    use MarcinOrlowski\ResponseBuilder\ResponseBuilder;


Methods' arguments:

 * `$data` (mixed|null) data you want to be returned in response's `data` node,
 * `$http_code` (int) valid HTTP return code (see `HttpResponse` class for useful constants),
 * `$lang_args` (array) array of arguments passed to `Lang::get()` while building `message`,
 * `$error_code` (int) error code you want to be returned in `code`,
 * `$message` (string) custom message to be returned as part of error response.

Most arguments of `success()` and `error()` methods are optional, with exception for `$error_code`
for the latter. Helper methods arguments are partially optional - see signatures below for details.

**NOTE:** Since v2.1 the requirement for `$data` to be an `array` is lifted and `$data` can be
of any type you need (i.e. `string`), however to ensure returned JSON structure is unaffected,
data type casting is used internally. There's no smart logic but ordinary `$data = (object)$data;`
casting with the exception for Laravel types like `Model` and `Collection`), and it's recommended
you ensure `$data` is `array` (with mentioned exception) if you do not want to end up with dictionary
using keys like "0" or "scalar".

**IMPORTANT:** If you want to return own value of `$http_code` with the response data, ensure used
value matches W3C meaning of the code. ResponseBuilder will throw `\InvalidArgumentException` if 
you try to call `success()` and  related methods with `$http_code` not being in range of 200-299. 
The same will happen if you try to call `error()` but `$http_code` will be lower than 400.

Redirection codes 3xx cannot be used with ResponseBuilder.

See [W3 specs page](https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html) for more details on HTTP codes.

#### Reporting Success ####

    success($data = null, $http_code = HttpResponse::HTTP_OK, array $lang_args = []);
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

To report success from your API, just conclude your Controller method with simple:

    return ResponseBuilder::success();

which will produce and return the following JSON object:

    {
      "success": true,
      "code": 0,
      "locale": "en",
      "message": "OK",
      "data": null
    }

If you would like to return some data with your success response (which pretty much always the case :), wrap it into `array` and pass it to `success()` as argument:

    $data = [ "foo" => "bar" ];
    return ResponseBuilder::success($data);

which would return:

    {
      "success": true,
      "code": 0,
      "locale": "en",
      "message": "OK",
      "data": {
          "foo": "bar"
      }
    }

Since v2.1 you can pass Eloquent model object directly for its data to be returned:

    $flight = App\Flight::where('active', 1)->first();
    return ResponseBuilder::success($flight);

which would return model attributes (`toArray()` will automatically be called by ResponseBuilder). The
imaginary output would then look like this:

    {
      "item": {
          "airline": "lot",
          "flight_number": "lo123",
          ...
       }
    }

You can also return the whole Collection if needed:

    $flights = App\Flight::where('active', 1)->get();
    return ResponseBuilder::success($flights);

which would return array of objects as expected:

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
    }

    
`item` and `items` keys are configurable - see `app/config/response_builder.php` or, if you already
published config file, look into your `vendor/marcin-orlowski/laravel-api-response-builder/config/`
folder for distribution config file and diff it for new configuration keys read. You can also read 
project changelog.

**NOTE:** currently there's no recursive processing implemented, so if you want to return Eloquent 
model as part of own array structure you must explicitly call `toArray()` on such object
prior adding it to your array you want to pass to ResponseBuilder:

    $data = [ 'flight' = App\Flight::where('active', 1)->first()->toArray() ];
    return ResponseBuilder::success($data);


**IMPORTANT:** `data` node is **always** returned as JSON Object. This is **enforced** by design, 
therefore trying to return plain array:

    $returned_array = [1,2,3];
    return ResponseBuilder::success($returned_array);

would, due to array-to-object conversion, produce the following output:

    {
      ...
      "data": {
         "0": 1,
         "1": 2,
         "2": 3
      }
    }

To avoid this you need to make the array part of object, which
usually means wrapping it into another array:

    $returned_array = [1,2,3];
    $data = ['my_array' => $returned_array];
    return ResponseBuilder::success($data);

This would produce expected and much cleaner data structure:

    {
      ...
      "data": {
         "my_array": [1, 2, 3]
      }
    }


#### Errors ####

Returning errors is almost as simple as returning success, however you need to provide at least error
code to `error()` method which will be then reported back to caller. To keep your source readable and clear, 
it's strongly suggested to create separate class i.e. `app/ErrorCode.php` and put all codes you need to use
in your code there as `const` and then reference it. This way you protect yourself from using wrong code or
save your time in case you will need to refactor code range in future. For example, your imaginary 
`app/ErrorCode.php` can look like this:

    <?php

    namespace App;

    class ErrorCode {
        const SOMETHING_WENT_WRONG = 250;
    }

End then, to report failure because of `SOMETHING_WENT_WRONG`, just reference this constant:

    return ResponseBuilder::error(ErrorCode::SOMETHING_WENT_WRONG);

This will produce the following JSON response:

    {
      "success": false,
      "code": 250,
      "locale": "en",
      "message": "Error #250",
      "data": null
    }

Please note the `message` key in the above JSON. ResponseBuilder tries to automatically obtain error
message for each code you pass. This is all configured in `config/response_builder.php` file, with
use of `map` array. See [ResponseBuilder Configuration](#response-builder-configuration) for more details.
If there's no dedicated message configured for given error code, `message` value is provided with use 
of built-in generic fallback message "Error #xxx", as shown above.

As ResponseBuilder uses Laravel's `Lang` package for localisation, you can use the same features with
your messages as you use across the whole application, including message placeholders:

    return ResponseBuilder::error(ErrorCode::SOMETHING_WENT_WRONG, ['login' => $login]);
    
and if message assigned to `SOMETHING_WENT_WRONG` code uses `:login` placeholder, it will be 
correctly replaced with content of your `$login` variable.

You can, however this is not really recommended, override built-in error message mapping too as
ResponseBuilder comes with `errorWithMessage()` method, which expects string message as argument.
This means you can just pass any string you want and it will be returned as `message` element
in JSON response regardless the `code` value. Please note this method is pretty low-level and string
is used as is without any further processing. If you want to use `Lang`'s placeholders here, you need
to handle them yourself by calling `Lang::get()` manually first and pass the result:

    $msg = Lang::get('message.something_wrong', ['login' => $login]);
    return ResponseBuilder::errorWithMessage(ErrorCode::SOMETHING_WENT_WRONG, $msg);


## Installation and Configuration ##

To install ResponseBuilder all you need to do is to open your shell/cmd and do:

    composer require marcin-orlowski/laravel-api-response-builder

If you want to change defaults then you should publish configuration file to 
your `config/` folder once package is installed:

    php artisan vendor:publish

and tweak this file according to your needs. If you are fine with defaults, this step
can safely be skipped (you can also remove published `config/response_builder.php` file).


#### Laravel setup ####

Edit `app/config.php` and add the following line to your `providers` array:

    MarcinOrlowski\ResponseBuilder\ResponseBuilderServiceProvider::class,


#### ResponseBuilder Configuration ####

ResponseBuilder configuration can be found in `config/response_builder.php` file and 
each of its element is heavily documented in the file itself. 

Supported configuration keys (all keys **MUST** be present in config file):

 * `min_code` (int) lowest allowed code for assigned code range (inclusive)
 * `max_code` (int) highest allowed code for assigned code range (inclusive)
 * `map` (array) maps error codes to localization string keys.

Code to message mapping example:

    'map' => [
        ErrorCode::SOMETHING => 'api.something',
    ],

If given error code is not present in `map`, ResponseBuilder will provide fallback message automatically 
(default message is like "Error #xxx"). This means it's perfectly fine to have whole `map` array empty in
your config, however you **MUST** have `map` key present nonetheless:

    'map' => [],

Also, read [Overriding built-in messages](#overriding-built-in-messages) to see how to override built-in
messages.


## Messages and Localization ##

ResponseBuilder is designed with localization in mind so default approach is you just set it up
once and most things should happen automatically, which also includes creating human readable error messages.
As described in `Configuration` section, once you got `map` configured, you most likely will not
be in need to manually refer error messages - ResponseBuilder will do that for you and you optionally
just need to pass array with placeholders' substitution (hence the order of arguments for `errorXXX()`
methods). ResponseBuilder utilised standard Laravel's `Lang` class to deal with messages, so all features
localization are supported.


## Handling Exceptions API way ##

Properly designed REST API should never hit consumer with anything but JSON. While it looks like easy task, 
there's always chance for unexpected issue to occur. So we need to expect unexpected and be prepared when 
it hit the fan. This means not only things like uncaught exception but also Laravel's maintenance mode can 
pollute returned API responses which is unfortunately pretty common among badly written APIs. Do not be 
one of them, and take care of that in advance with couple of easy steps. 
With Laravel this can be achieved with custom Exception Handler and ResponseBuilder comes with ready-to-use
Handler as well. See [EXCEPTION_HANDLER.md](EXCEPTION_HANDLER.md) for easy setup information.


## Manipulating Response Object ##

If you need to return more fields in response object you can simply extend `ResponseBuilder` class
and override `buildResponse()` method:

    protected static function buildResponse($code, $message, $data = null);

For example, you want to get rid of `locale` field and add server time and timezone to returned
responses. First, create `MyResponseBuilder.php` file in `app/` folder (both location and class
name can be anything you wish, just remember to adjust the namespace too) and override
`buildResponse()` method which builds normalized response array for all the helper methods.
So the class content should be as follow:

    <?php

    namespace App;

    class MyResponseBuilder extends MarcinOrlowski\ResponseBuilder\ResponseBuilder
    {
        protected static function buildResponse($code, $message, $data = null)
        {
            // tell ResponseBuilder to do all the dirty job first
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

and from now on use `MyResponseBuilder` class instead of `ResponseBuilder`:

    MyResponseBuilder::success();

which should then return your desired JSON structure:

     {
       "success": true,
       "code": 0,
       "message": "OK",
       "timestamp": 1272509157,
       "timezone": "UTC",
       "data": null
     }


## Overriding built-in messages ##

At the moment ResponseBuilder provides few built-in messages (see [src/ErrorCode.php](src/ErrorCode.php)):
one is used for success code `0` and another provides fallback message for codes without custom mapping. If for 
any reason you want to override them, simply map these codes in your `map` config using codes from package
reserved range:

     MarcinOrlowski\ResponseBuilder\ErrorCode::OK => 'my_messages.ok',

and from now on, each `success()` will be returning your message instead of built-in one.

To override default error message used when given error code has no entry in `map`, add the following:

     MarcinOrlowski\ResponseBuilder\ErrorCode::NO_ERROR_MESSAGE => 'my_messages.default_error_message',

You can use `:error_code` placeholder in the message and it will be substituted actual error code value.

## Contributing ##

Please report any issue spotted using [GitHub's project tracker](https://github.com/MarcinOrlowski/laravel-api-response-builder/issues).
 
If you'd like to contribute to the this project, please [open new ticket](https://github.com/MarcinOrlowski/laravel-api-response-builder/issues) **before writting any code**. This will help us save your
time in case I'd not be able to accept such changes. But if all is good and clear then follow common routine:

 * fork the project
 * create new branch
 * do your changes
 * send pull request

Thanks in advance!

## License ##

* Written and copyrighted &copy;2016-2017 by Marcin Orlowski <mail (#) marcinorlowski (.) com>
* ResponseBuilder is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)


## Notes ##

* ResponseBuilder is **not** compatible with Lumen framework, mainly due to lack of Lang class. If you would like to help making ResponseBuilder usable with Lumen, speak up or (better) send pull request!
* Tests will be released shortly. They do already exist, however ResponseBuilder was extracted from existing project and making tests work again require some work to remove dependencies.

## Changelog ##

 See [CHANGES.md](CHANGES.md) for detailed revision history.
