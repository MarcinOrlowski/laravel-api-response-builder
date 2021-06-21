![REST API Response Builder for Laravel](../artwork/laravel-api-response-builder-logo.svg)

# Exposed methods #

[« Documentation table of contents](README.md)

 * [Exposed Methods](methods.md)
   * [Builder](#builder)
     * [Static builder methods](#builder-static)
   * [Helpers](#helpers)

---

## Exposed Methods ##

 Starting from version 6.4, `ResponseBuilder` uses new API implementation that uses
 [Builder pattern](https://en.wikipedia.org/wiki/Builder_pattern), which is far more flexible that previous bag of methods.

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

### Builder static ###

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
 * `withData($data)`: (**mixed**) data you want to be returned in your response in `data` node,
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

 > ![IMPORTANT](img/warning.png) To enforce constant JSON structure of the response, `data` node is always an JSON object,
 > therefore passing anything but `object` or `array` to `withData()` would trigger internal type casting. There's no smart
 > logic here, just ordinary `$data = (object)$data;`. The only exception are classes configured with "classes" mapping 
 > (see configuration details). In such case configured conversion method is called on the provided object and result is
 > returned instead. Several classes pre-configured but you can add additional classes just by creating entry in configuration
 > `converter` mapping. See [Data Conversion](conversion.md) for more information.

## Helpers ##

 But while [Builder](https://en.wikipedia.org/wiki/Builder_pattern) pattern used is pretty proverful interface, if
 you need to return just success or error, without too many additional data attached, using it may look like overkill,
 therefore there are two helper methods (from old API) that serve as shortcuts for reporting success or failure:

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

