![REST API Response Builder for Laravel](img/logo.png)

# Configuration file #
 If you want to change `ResponseBuilder` default configuration you need to use config file. Use package provided configuration
 template and publish `response_builder.php` configuration template file to your `config/` folder:

    php artisan vendor:publish

 If you are fine with the defaults, this step can safely be omitted. You can also remove published `config/response_builder.php`
 file if exists.

# Configuration options #

 Available configuration options and its current default values listed in alphabetical order. Please note, that in majority
 of use cases it should be perfectly sufficient to just use defaults and only tune the config when needed.
 
 * [classes](#classes)
 * [debug](#debug)
 * [encoding_options](#encoding_options)
 * [exception_handler](#exception_handler)
 * [map](#map)
 * [min_code](#min_code)
 * [max_code](#max_code)

## classes ##
 
`Response Builder` can auto-convert to be used as response `data`. Create new entry for each class you want to have supported
The entry key is a class name to check passed `data` object against, and configuration elements include:

```php
'classes' => [
    Namespace\Classname::class => [
        'method' => 'toArray',
        'key'    => 'items',
        ],
],
```
Where `method` is a name of the method to that `ResponseBuilder` should call on the object to obtain array representation of its 
internal state, while `key` is a string that will be used as the JSON response as key to array representation.

**NOTE:** order or entries matters as matching is done in order of appearance and is done using PHP `instanceof`. 
So if you have class `A` and `B` that extends `A` and you want different handling for `B` than you have set for `A` 
then `B` related configuration must be put first.

See [Data Conversion](docs.md#data-conversion) docs for closer details wih examples.
 
## debug ##

```php
'debug' => [
    'debug_key' => 'debug',

    'exception_handler' => [
           'trace_key' => 'trace',
           'trace_enabled' => env('APP_DEBUG', false),
    ],
],
```

`debug_key` - name of the JSON key trace data should be put under when in `debug` node.

	/**
	 * When ExceptionHandler kicks in and this is set to @true,
	 * then returned JSON structure will contain additional debug data
	 * with information about class name, file name and line number.
	 */

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
## encoding_options ##

 This option controls data JSON encoding. Since v3.1, encoding was relying on framework's defaults, however this
 caused valid UTF-8 characters (i.e. accents) to be returned escaped, which, while technically correct,
 and theoretically transparent) might not be desired.

 To prevent escaping, add JSON_UNESCAPED_UNICODE:
 
     JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE

 Laravel's default value:
 
    JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT

 See [json_encode() manual](http://php.net/manual/en/function.json-encode.php) for more details.

## exception_handler ##

 If you use `ResponseBuilder`'s Exception handler helper, you must map all the exceptions handled to unique api code
 from your currently configured range. That allows API calls chaining with proper error failure handling up to the
 top client code.
 
```php
'exception_handler' => [
    'exception' => [
        'http_not_found' => [
            'code'      => \App\ApiCodes::HTTP_NOT_FOUND(),
            'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_BAD_REQUEST,
        ],
        ...
    ]
]
```


Default exception handling configuration:

```php
'exception_handler' => [
    'exception' => [
        'http_not_found' => [
            'code'      => \App\ApiCodes::HTTP_NOT_FOUND(),
            'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_BAD_REQUEST,
        ],
        'http_service_unavailable' => [
            'code'      => \App\ApiCodes::HTTP_SERVICE_UNAVAILABLE(),
            'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_BAD_REQUEST,
        ],
        'http_exception' => [
            'code'      => \App\ApiCodes::HTTP_EXCEPTION(),
            'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_BAD_REQUEST,
        ],
        'uncaught_exception' => [
            'code'      => \App\ApiCodes::UNCAUGHT_EXCEPTION(),
            'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_INTERNAL_SERVER_ERROR,
        ],
        'authentication_exception' => [
            'code'      => \App\ApiCodes::AUTHENTICATION_EXCEPTION(),
            'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_UNAUTHORIZED,
        ],
        'validation_exception' => [
            'code'      => \App\ApiCodes::VALIDATION_EXCEPTION(),
            'http_code' => Symfony\Component\HttpFoundation\Response\::HTTP_UNPROCESSABLE_ENTITY,
        ],
    ],
```

## map ##

`ResponseBuilder` can automatically use text error message associated with error code and return in the
response, once its configured to know which string to use for which code. `ResponseBuilder` uses standard
Laravel's `Lang` facade to process strings.

```php
'map' => [
	ApiCode::SOMETHING => 'api.something',
	...
],
```
	
See [Exception Handling with Response Builder](docs/exceptions.md) if you want to provide own messages for built-in codes.

## min_code ##

 This option defines lowest allowed (inclusive) code that can be used.

 NOTE ResponseBuilder reserves first 19 codes for its own needs. First code you can use is 20th code in your pool.

```php
'min_code' => 100,
```

## max_code ##

 Min api code in assigned for this module (inclusive)
 This option defines highest allowed (inclusive) code that can be used.

```php
'max_code' => 1024,
```
