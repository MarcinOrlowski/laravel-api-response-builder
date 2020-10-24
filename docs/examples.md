![REST API Response Builder for Laravel](img/logo.png)

# REST API Response Builder for Laravel #

 `ResponseBuilder` is [Laravel](https://laravel.com/)'s helper designed to build
 nice, normalized and easy to consume REST API JSON responses.

## Table of contents ##

 * [Usage examples](#usage-examples)
   * [Use clause](#use-clause)
   * [Success](#success)
   * [Errors](#errors)

----

## Usage examples ##

 The following examples assume `ResponseBuilder` is properly installed and available to your Laravel application.
 See [Installation and Configuration](installation.md) for more details.

### Use clause ###

 Basic, and mosts common usage examples.

#### Success ####

 To create response indicating operation success, simply conclude your Controller method with:

```php
return RB::success();
```

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

 If you would like to return some data in your response, which is pretty much always the case for success type of responses:), pass  
 your payload as `success()`'s argument:

```php
$data = [ 'foo' => 'bar' ];
return RB::success($data);
```

 This which would produce:

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

 **NOTE:** As all the data in the response structure must strictly follow response structure and end up in form os valid JSON data.
 `ResponseBuilder` deals with all the primitives and most commonly used classes, using on-the-fly data conversion. You can easily
 add own converters if none of built-in handles your data or fits your needs. See [Data Conversion](conversion.md) for more details.


```php
$returned_array = [1,2,3];
return RB::success($data);
```

 which would give:

```json
{
   ...
   "data": {
      "items": [1, 2, 3]
   }
}
```

#### Errors ####

 Returning error responses is also simple, however in such case you are required to need to additionally pass at least your own
 error code to `error()` to tell the client what the error it is:

```php
return RB::error(<CODE>);
```

 To make your life easier (and your code [automatically testable](testing.md)) you should put all error codes you use
 in separate `ApiCodes` class, as its `public const`s, which would improve code readability and would prevent certain
 types of coding error from happening. Please see [Installation and Configuration](#installation-and-configuration)
 for details.

 Example usage:

```php
return RB::error(ApiCode::SOMETHING_WENT_WRONG);
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

```php
return RB::error(ApiCodeBase::SOMETHING_WENT_WRONG, ['login' => $login]);
```

 and if message assigned to `SOMETHING_WENT_WRONG` code uses `:login` placeholder, it will be correctly replaced with content of
 your `$login` variable.

 You can, however this is not recommended, override built-in error message mapping too as `ResponseBuilder` comes with
 `errorWithMessage()` method, which expects string message as argument. This means you can just pass any string you want and
 it will be returned as `message` element in JSON response regardless the `code` value. Please note this method is pretty
 low-level and string is used as is without any further processing. If you want to use `Lang`'s placeholders here, you need
 to handle them yourself by calling `Lang::get()` manually first and pass the result:

```php
$msg = Lang::get('message.something_wrong', ['login' => $login]);
return RB::errorWithMessage(ApiCodeBase::SOMETHING_WENT_WRONG, $msg);
```
