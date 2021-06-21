![REST API Response Builder for Laravel](../artwork/laravel-api-response-builder-logo.svg)

# Data conversion #

[« Documentation table of contents](README.md)

 * [On-the-fly data conversion](#on-the-fly-data-conversion)
   * [Examples](#examples)
   * [Configuration](#configuration)
   
---

# On-the-fly data conversion #

 `ResponseBuilder` can save you some work by automatically converting objects into array representation. For example, having
 `ResponseBuilder` configured to auto-convet objects of Eloquent's `Model` class and passing object of that class either directly
 using `withData()` or as part of bigger structurre) will have it converted to JSON format automatically.

 The following classes are supported out of the box:

 * `\Illuminate\Database\Eloquent\Model`
 * `\Illuminate\Support\Collection`
 * `\Illuminate\Database\Eloquent\Collection`
 * `\Illuminate\Http\Resources\Json\JsonResource`
 * `\Illuminate\Pagination\LengthAwarePaginator`
 * `\Illuminate\Pagination\Paginator`

## Examples ##

 Passing single model, like this:

```php
$flight = App\Flight::where(...)->first();
return RB::success($flight);
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

 If you have more data, then pass the `Collection`:

```php
$flights = App\Flight::where(...)->get();
return RB::success($flights);
```

 which would return array of your objects:

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

## Configuration ##

 The whole functionality is configurable using `converter` array. See [Converter configuration](config.md#converter) for
 more information.

