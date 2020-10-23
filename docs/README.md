![REST API Response Builder for Laravel](docs/img/logo.png)

# REST API Response Builder for Laravel #

## Table of contents ##

 * [Usage examples](#usage-examples)
 * [Detailed documentation](docs.md)
 * [Requirements](installation.md#requirements)
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

