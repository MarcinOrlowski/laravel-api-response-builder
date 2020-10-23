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

