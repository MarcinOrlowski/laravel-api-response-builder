![REST API Response Builder for Laravel](img/logo.png)

# REST API Response Builder for Laravel #

 `ResponseBuilder` is [Laravel](https://laravel.com/)'s helper designed to build
 nice, normalized and easy to consume REST API JSON responses.

## Requirements ##

 Minimum requirements:

  * PHP 7.2+ with [json extension](https://www.php.net/manual/en/book.json.php),
  * Laravel v6.x or v7.x (see [legacy](legacy.md) for Laravel 5.x support).

----

## Installation and Configuration ##

 To install `ResponseBuilder` all you need to do is to open your shell/cmd and do:

```bash
composer require marcin-orlowski/laravel-api-response-builder:<VERSION>
```

 Where `<VERSION>` string consists of `MAJOR` and `MINOR` release numbers. For
 example if current relase is 6.4.13, you need to invoke:

```bash
composer require marcin-orlowski/laravel-api-response-builder:6.4
```

 which will add  the dependency at the release 6.3 + all the bugfixing releses
 (`6.3.*`) but won't automatically pull 6.4 even if available, unless
 `composer.json` is updated manually.

 If you want to use different configuration than `ResponseBuilder` defaults,
 publish and edit configuration file as described in [Configuration file](config.md)
 documentation.

#### Setup ####

 `ResponseBuilder` supports Laravel's auto-discovery feature and it's ready to use once
 installed.


#### ResponseBuilder Configuration ####

 Package configuration can be found in `config/response_builder.php` file and
 each of its element is heavily documented in the file, so please take a moment
 and read it.

 Supported configuration keys (all keys **MUST** be present in config file):

 * `min_code` (int) lowest allowed code for assigned code range (inclusive)
 * `max_code` (int) highest allowed code for assigned code range (inclusive)
 * `map` (array) maps error codes to localization string keys.

 Code to message mapping example:

```php
'map' => [
    ApiCode::SOMETHING_WENT_WRONG => 'api.something_went_wrong',
],
```

 If given error code is not present in `map`, `ResponseBuilder` will provide fallback message automatically
 (default message is like "Error #xxx"). This means it's perfectly fine to have whole `map` array empty in
 your config, however you **MUST** have `map` key present nonetheless:

```php
'map' => [],
```

 Also, read [Overriding built-in messages](#overriding-built-in-messages) to see how to override built-in
 messages.

 **NOTE:** Config file may grow in future so if you are not using defaults, then on package upgrades
 check CHANGES.md to see if there're new configuration options. If so, and you already have config
 published, then you need to look into dist config file in `vendor/marcin-orlowski/laravel-api-response-builder/config/`
 folder and grab new version of config file.


