![REST API Response Builder for Laravel](../artwork/laravel-api-response-builder-logo.svg)

# Response object #

[« Documentation table of contents](README.md)

 * [Response object](response.md)
   * [Manipulating Response Object](#manipulating-response-object)
   * [Custom response structure](#custom-response-structure)

## Manipulating Response Object ##

 If you need to return more fields in response object you can simply extend `ResponseBuilder` class
 and override `buildResponse()` method.

## Custom response structure ##

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

