![REST API Response Builder for Laravel](../artwork/laravel-api-response-builder-logo.svg)

# Configuration #
w
[« Documentation table of contents](README.md)

 * [Configuration file](#configuration-file)
   * [Configuration options](#configuration-options)
     * [converter](#converter)
       * [classes](#classes)
       * [primitives](#primitives)
     * [debug](#debug)
     * [encoding_options](#encoding_options)
     * [exception_handler](#exception_handler)
     * [map](#map)
     * [min_code](#min_code)
     * [max_code](#max_code)

---

# Configuration file #

 At runtime `ResponseBuilder` looks for `response_builder.php` configuration file in your application
 `config/` folder and falls back to defaults if no config file is found. Please see [Installation](installation.md)
 docs for more info how to properly set up config file.

## Configuration options ##

 Available configuration options and its current default values listed in alphabetical order. Please note, that in majority
 of use cases it should be perfectly sufficient to just use defaults and only tune the config when needed.

 * [converter](#converter)
   * [classes](#classes)
   * [primitives](#primitives)
 * [data_always_object](#data_always_object)
 * [debug](#debug)
 * [encoding_options](#encoding_options)
 * [exception_handler](#exception_handler)
 * [map](#map)
 * [min_code](#min_code)
 * [max_code](#max_code)

### converter ###

 `Response Builder` can auto-convert data to be used as response `data`. It supports both primitives and objects of
 any classes that have corresponding converter configured.

#### classes ####

 Create new entry for each class you want to have supported. The entry key is a full class name (including namespace):

```php
'converter' => [
    \Illuminate\Database\Eloquent\Model::class => [
        'handler' => \MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter::class,
        'key'     => 'items',
        'pri'     => 0,
    ],
    \Illuminate\Pagination\Paginator::class => [
        'handler' => \MarcinOrlowski\ResponseBuilder\Converters\ArrayableConverter::class,
        'key'     => null,  // SPECIAL CASE. READ BELOW!
        'pri'     => 0,
    ],
],
```

 Meaning of parameters:

 * `handler` (mandatory) specifies a full name of the class implementing `ConverterContract`. Object of that class will be
   instantiated and conversion method invoked with object to convert passed as method argument.
 * `key` (mandatory), value can be a string or (in some special cases, `NULL` is also allowed). 

> ![IMPORTANT](img/warning.png) Object conversion works **recursively**! That makes some implications, when you i.e.
> convert object (say `A`) which when converted still returns other objects  (say 'B`s). In such case once `ResponseBuilder`
> will also try to convert `B` so final JSON representation contain plain JSON structures. So when converting an object of a
> given class that is going to be returned directly as response payload (i.e. `RB::success($collection);`), the string given
> as `key` will be used in returned JSON as converted data key. The only case where you can have key set to `NULL` is when 
> you are sure you are dealing i.e. collection of objects, so the collection object will be `key`ed in the response (as per
> its class' configuration), but each element of that collection should be returned as plain JSON array.
> In other words, you can only have `NULL` key value if your objects are part of other structures. You cannot have it
> on main objects, because `ResponseBuilder` would not be able to construct valid JSON Object without the key.

```json
   ...
   "data": {
       "given-key": {
            [converted object data]
       }
   }
   ...
```

 * `pri` (optional) is an integer being entry's priority (default `0`). Entries with higher values will be matched first. If you got one
   class extending another and you want to support both of them with separate configuration, then you **must** ensure child
   class has higher priority than it's parent class.

 All configuration entries are assigned priority `0` which can be changed using `pri` key (integer). This value is used to
 sort the entries to ensure that matching order is preserved. Entries with higher priority are matched first etc. This is
 very useful when you want to indirect configuration for two classes where additionally second extends first one.
 So if you have class `A` and `B` that extends `A` and you want different handling for `B` than you have set for `A`
 then `B` related configuration must be set with higher priority.

 > ![IMPORTANT](img/warning.png) For each object `ResponseBuilder` checks if we have configuration entry matching **exactly**
 > object class name. If no such mapping is found, then the whole configuration is walked again, but this time we take inheritance
 > into consideration and use `instanceof` to see if we have a match, therefore you need to pay attention your config specifies
 > lower priority (i.e. `-10`) for all the generic handlers. Doing that ensures any more specific handler will be checked
 > first. If no handler is found for given object, the exception is thrown.

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

 See [Data Conversion](conversion.md) docs for closer details wih examples.

 > ![NOTE](img/notes.png) In case of data conversion problems add `RB_CONVERTER_DEBUG=true` entry to your `.env` file
 > (also see [debug](#debug) for related config options) then peek Laravel log to see what converter was used for each
 > type of data and why it was choosen.

#### primitives ####

 Starting from v9, `ResponseBuilder` suppors passing primitives as direct payload, removing the need of wrapping it in separate
 container (like array or object). The following primitives are supported:

 * `array`
 * `boolean`
 * `double`
 * `integer`
 * `string`

 For each of these types there's configuration entry in `primitives` node of `converter` config. Each entry defined `key`
 which is an arbitrary string, used for given primitive. The default value for all supported primitives is `value`.
 For example, pre v9 would require

    RB::success(['value' => 12.25]);

while with v9+ if can be simplified:

    RB::success(12.25);

and both would yield the same result:

```json
{
  "success": true,
  "code": 0,
  "locale": "en",
  "message": "OK",
  "data": {
      "value": 12.25
  }
}
```

### data_always_object ###

Added in v9.3.0

By default, when no payload is to be returned (i.e. `RB::success();`), the `data` node of the JSON response would be returned
with the value of `NULL`:

```json
{
  "success": true,
  "code": 0,
  "locale": "en",
  "message": "OK",
  "data": null
}
```

When this option is set to `TRUE`, the `data` will be always valid JSON object, even if no data is to be returned. In such
case the object will be returned empty:

```json
{
  "success": true,
  "code": 0,
  "locale": "en",
  "message": "OK",
  "data": {}
}
```

### debug ###

 > ![NOTE](img/warning.png) Do not use debug mode on production as it may expose i.e. your filesystem structure
 > or class names or other internals that should not really be public.

```php
'debug' => [
    'debug_key' => 'debug',

    'exception_handler' => [
           'trace_key' => 'trace',
           'trace_enabled' => env('APP_DEBUG', false),
    ],

    // Controls debugging features of payload converter class.
    'converter' => [
        // Set to true to figure out what converter is used for given data payload and why.
        'debug_enabled' => env('RB_CONVERTER_DEBUG', false),
    ],

],
```

`debug_key` - name of the JSON key trace data should be put under when in `debug` node.
When `ExceptionHandler` kicks with debug mode enabled, returned JSON structure would
contain additional debug data with information about class name, file name and line number:

```json
{
    "success": false,
    "code": 0,
    "locale": "en",
    "message": "Uncaught Exception",
    "data": null,
    "debug": {
        "trace": {
            "class": "<EXCEPTION CLASS NAME>",
            "file": "<FILE THAT CAUSED EXCEPTION>",
            "line": "<LINE NUMBER>"
        }
    }
}
```

### encoding_options ###

 This option controls data JSON encoding. Since v3.1, encoding was relying on framework's defaults, however this
 caused valid UTF-8 characters (i.e. accents) to be returned escaped, which, while technically correct,
 and theoretically transparent) might not be desired.

 To prevent escaping, add JSON_UNESCAPED_UNICODE:

```php
JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE
```

 Laravel's default value:

```php
JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT
```

 See [json_encode() manual](http://php.net/manual/en/function.json-encode.php) for more details.

### exception_handler ###

 `ResponseBuilder`'s Exception handler helper is plug-and-play helper that will automatically handle
 any exception thrown by your code and expose valid JSON response to the client applications. But aside
 from error handling, some programmers use exceptions to quickly break the flow and return with additional
 information. In such case you may want to assign separate API code to each of these "special" exceptions
 and this is where `exception_handler` section comes in.

 `ResponseBuilder` delegates handling of exceptions to dedicated handlers which lets you add your own
 when needed. Each configuration entry consits of name of the handler, its priority (which is useful if you
 deal with inherited exception classes) and optional configuration (depending on the handler):

```php
'exception_handler' => [
    \Symfony\Component\HttpKernel\Exception\HttpException::class => [
        'handler' => \MarcinOrlowski\ResponseBuilder\ExceptionHandlers\HttpExceptionHandler::class,
        'pri'     => -100,
        'config'  => [
            HttpException::class => [
                // used by unauthenticated() to obtain api and http code for the exception
                HttpResponse::HTTP_UNAUTHORIZED => [
                    'api_code' => ApiCodes::YOUR_API_CODE_FOR_UNATHORIZED_EXCEPTION,
                ],
                // default handler is mandatory and MUST have both `api_code` and `http_code` set.
                'default' => [
                'api_code'  => ApiCodes::YOUR_API_CODE_FOR_GENERIC_HTTP_EXCEPTION,
                    'http_code' => HttpResponse::HTTP_BAD_REQUEST,
                ],
            ],
        ],
    ],
],
```


 At runtime, exception handler will look for config entry for particualr exception class and use dedicated handler if found. If
 no exact match exists, it will try to match the handler using `instanceof` and eventually faill back to default handler
 as specified in (mandatory) `default` config node.

### map ###

 `ResponseBuilder` can automatically use text error message associated with error code and return in the
 response, once its configured to know which string to use for which code. `ResponseBuilder` uses standard
 Laravel's `Lang` facade to process strings.

```php
'map' => [
	ApiCode::SOMETHING => 'api.something',
	...
],
```

 If given error code is not present in `map`, `ResponseBuilder` will provide fallback message automatically
 (default message is like "Error #xxx"). This means it's perfectly fine to have whole `map` array empty in
 your config, however you **MUST** have `map` key present nonetheless:

```php
'map' => [],
```

 Also, read [Overriding built-in messages](docs.md#overriding-built-in-messages) to see how to override built-in
 messages.

 **NOTE:** Config file may grow in future so if you are not using defaults, then on package upgrades
 check CHANGES.md to see if there're new configuration options. If so, and you already have config
 published, then you need to look into dist config file in `vendor/marcin-orlowski/laravel-api-response-builder/config/`
 folder and grab new version of config file.

 See [Exception Handling with Response Builder](exceptions.md) if you want to provide own messages for built-in codes.

### min_code ###

 This option defines lowest allowed (inclusive) code that can be used.

 > ![NOTE](img/warning.png) ResponseBuilder reserves first 19 codes for its own needs. First code you can use is
 > 20th code in your pool.

```php
'min_code' => 100,
```

### max_code ###

 Min api code in assigned for this module (inclusive). This option defines the highest allowed (inclusive) code that can be used.

```php
'max_code' => 1024,
```

