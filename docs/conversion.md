![REST API Response Builder for Laravel](img/logo.png)

# On-the-fly data conversion #

 `ResponseBuilder` can save you some work by automatically converting objects into array representation. For example, having
 `ResponseBuilder` configured to auto-convet objects of Eloquent's `Model` class and passing object of that class either directly
 using `withData()` or as part of bigger structurre) will have it converted to JSON format automatically:

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

 Or you have more data, then pass `Collection`:

```php
$flights = App\Flight::where(...)->get();
return RB::success($flights);
```

 which would return array of objects:

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

 The whole functionality is configurable using `converter` array:

```php
'converter' => [
    \Illuminate\Database\Eloquent\Model::class          => [
        'handler' => \MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter::class,
        'key'     => 'items',
        // 'pri'     => 0,
    ],
    \Illuminate\Database\Eloquent\Collection::class     => [
        'handler' => \MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter::class,
        'key'     => 'items',
        // 'pri'     => 0,
    ],
],
```

 Meaning of parameters:

 * `handler` (mandatory) specifies class name that implements `ConverterContract` interface that is capable of doing the
   conversion of object of given class.
 * `key` (mandatory) is a string, used by some converters when dealing with object of given class being returned directly
   as response payload (i.e. `success($collection)`).
 * `pri` (optional) is an integer being entry's priority (default `0`). Entries with higher values will be matched first. If you got one
   class extending another and you want to support both of them with separate configuration, then you **must** ensure child
   class has higher priority than it's parent class.

 The above configures two classes (`Model` and `Collection`). Whenever object of that class is spotted, method specified in
 `method` key would be called on that object and data that method returns will be returned in JSON object.

 **IMPORTANT:** For each object `ResponseBuilder` checks if we have configuration entry matching **exactly** object class
 name. If no such mapping is found, then the whole configuration is walked again, but this time we take inheritance into
 consideration and use `instanceof` to see if we have a match, therefore you need to pay attention your config specifies
 lower priority (i.e. `-10`) for all the generic handlers. Doing that ensures any more specific handler will be checked
 first. If no handler is found for given object, the exception is thrown.

 When you pass the array it will be walked recursively and the conversion will take place on all known elements as well:

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

