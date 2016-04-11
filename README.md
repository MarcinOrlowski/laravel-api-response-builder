## API Response Builder for Laravel 5 ##
 
 Response Builder is Laravel5's helper designed to simplify building 
 nice, normalized and easy to consume REST API responses.
 
## Response structure ##

 For simplicity of consuming, produced JSON response is **always** the same at its corem and contains the following data:

  * `success` (boolean) determines if API method succeeded or not
  * `code` (int) being your own return code (see `Return Codes` and `Code Ranges` section below)
  * `locale` (string) locale used for error message (obtained automatically via \App::getLocale())
  * `message` (string) human readable description of `code` (see `Messages and Localization` section below)
  * `data` (object|null) your returned payload or `null` if there's no data to return.


## Return Codes ##

 Return codes must be positive integer. Code `0` (zero) always means success, all
 other codes are treaded as error codes. 

## Code Ranges ##

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
 
 NOTE: codes from `0` to `63` (inclusive) are reserved by Response Builder and
 cannot be used by your codes.
 
## Usage examples ##

### Success ###

To report success from your Controller just do:
 
    return ResponseBuilder::success();
 
which will produce
 
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

which would produce

    {
      "success": true,
      "code": 0,
      "locale": "en",
      "message": "OK",
      "data": {
         "foo": "bar"
      }
    }

**IMPORTANT** `data` node is **always** returned as JSON Object. This is by design, to simplify response 
API consumption, so if you want to return array, put it in another array:
 
    $data = [1,2,3];
    return ResponseBuilder::success( ['my_array'=>$data] );
    
which would produce

    {
      "success": true,
      "code": 0,
      "locale": "en",
      "message": "OK",
      "data": {
         "my_array": [1, 2, 3]
      }
    }

Nested arrays are supported.
    
### Errors ###

 Returning error is almost as simple as returning success, however you need to provide at least error 
 code. I strongly suggest not to use numeric values directly in your code but to create separate class,
 keep all used codes there and reference them:
  
    class ErrorCode {
        const SOMETHING_WENT_WRONG = 250;
    }

 To report failure of your method just do:

    return ResponseBuilder::error(ErrorCodes::SOMETHING_WENT_WRONG);
    
 which would produce

    {
      "success": false,
      "code": 250,
      "locale": "en",
      "message": "No description for error #250",
      "data": null
    }

 As there's no custom message, `message` field returns built-in message. To provide custom message you 
 need to edit add  entry for `ErrorCodes::SOMETHING_WENT_WRONG` to `map` array in Response Builder 
 configuration file.  See `Response Builder Configuration` section for details


## Exposed Methods ##

 All available methods are **static**. 
 
 For simplicity of use, it's recommended to add 
 
    use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
    
 to code that calls Response Builder methods.

 Expected arguments:

 * `$data` (array|null) data you want to be returned in response's `data` node,
 * `$http_code` (int) valid HTTP return code (see HttpResponse class for useful constants),
 * `$lang_args` (array) array of arguments passed to `Lang::get()` while building `message`,
 * `$error_code` (int) error code you want to be returned in `code`,
 * `$message` (string) custom message to be returned as part of error response.
 
 **NOTE** If you want to use own `$http_code`, ensure it is right for the purpose.
 Response Builder will throw `\InvalidArgumentException` if you use `$http_code` outside
 of 200-299 range with `success()` and related methods and it will also do the same
 for `error()` and related methods if `$http_code` will be lower than 400.
 
 Redirection codes 3xx cannot be used with Response Builder.
 
 See [W3 specs page](https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html) for more details.

### Reporting Sucess ###

    success(array $data = null, $http_code = HttpResponse::HTTP_OK, array $lang_args = []);
    successWithHttpCode($http_code);
    successWithDataAndHttpCode($http_code);
    
### Reporting Error ###

    error($error_code, $lang_args = [], $data = null, $http_code = HttpResponse::HTTP_BAD_REQUEST);
    errorWithData($error_code, $data, array $lang_args = []);
    errorWithDataAndHttpCode($error_code, $data, $http_code, array $lang_args = []);
    errorWithHttpCode($error_code, $http_code, $lang_args = []);
    errorWithMessage($error_code, $message, $http_code = HttpResponse::HTTP_BAD_REQUEST);


## Installation ##

 To install Response Builder all you need to do is to open your shell and do:

      composer require marcin-orlowski/laravel-api-response-builder

 Alternatively you can edit your `composer.json` and add the following line to `require` 
 section:
 
     "marcin-orlowski/laravel-api-response-builder": "~1.0",
 
 then call `composer update marcin-orlowski/laravel-api-response-builder` once you done. 
 

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

 **IMPORTANT** ALL return codes you want Response Builder to use without specifying
 return message manually MUST be "registered" in `map` otherwise `\InvalidArgumentException`
 will be thrown.


## Messages and Localization ##

 Response Builder utilised standard Laravel's Lang class to deal with messages, so all features
 are available, incl. support for message with placeholders.

## Lumen support ##

 Response Builder is **not** compatible with Lumen framework, mainly due to lack of Lang class.


## CHANGES ##

* v1.0.0 (2016-04-11)
   * Initial public release
