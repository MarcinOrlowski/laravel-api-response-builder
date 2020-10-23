![REST API Response Builder for Laravel](docs/img/logo.png)

# REST API Response Builder for Laravel #

[![Latest Stable Version](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/v/stable)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)
[![Build Status](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder.svg?branch=master)](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder)
[![Code Quality](https://scrutinizer-ci.com/g/MarcinOrlowski/laravel-api-response-builder/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/MarcinOrlowski/laravel-api-response-builder/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/MarcinOrlowski/laravel-api-response-builder/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/MarcinOrlowski/laravel-api-response-builder/?branch=master)
[![Codacy Grade Badge](https://api.codacy.com/project/badge/Grade/44f427e872e2480597bde0242417a2a7)](https://www.codacy.com/app/MarcinOrlowski/laravel-api-response-builder)
[![Monthly Downloads](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/d/monthly)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)
[![License](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/license)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)

## Table of contents ##

 * [Usage examples](#usage-examples)
 * [Documentation](docs.md)
 * [Requirements](docs.md#requirements)
 * [Installation and Configuration](docs.md#installation-and-configuration)

 **Upgrading from previous version? Check [compatibility docs](docs/compatibility.md) prior altering your `composer.json`!**

----

## Usage examples ##

 Operation successful? Conclude your controller method with:

```php
return RB::success();
```

 and your client will get nice JSON like

```json
{
  "success": true,
  "code": 0,
  "locale": "en",
  "message": "OK",
  "data": null
}
```

 Need to additionally return extra payload with the response? Pass it as
 argument to `success()`:

```php
$flight = App\Flight::where(...)->get();
return RB::success($flight); 
```

 and your client will get that data in `data` node of your response:

```json
{
  "success": true,
  "code": 0,
  "locale": "en",
  "message": "OK",
  "data": {
     "items": [
        {
          "airline": "lot",
          "flight_number": "lo123",
          ...
       },
       {
          "airline": "american",
          "flight_number": "am456",
          ...
       }
    ]
  }
}
```

 Something went wrong and you want to tell the clinet about that? Just do:

```php
return RB::error(250);
```

 The following JSON response will then be returned:

```json
{
   "success": false,
   "code": 250,
   "locale": "en",
   "message": "Your error message for code 250",
   "data": null
}
```

 Nice and easy! And yes, `message` can be easily customized! Also there're **much, much more** you can do with
 rich `ResponseBuilder` API. See [library documentation](docs/docs.md) for details and more examples!

