![REST API Response Builder for Laravel](img/logo.png)

# Installataion #

[« Documentation table of contents](README.md)

 * Installataion
   * [Package installation](#installation)
   * [Setup](#setup)
   * [Configuration file](#configuration-file)
   * [Notes](#notes)

---

## Installation ##

 To install `ResponseBuilder` all you need to do is to open your shell/cmd and do:

```bash
composer require marcin-orlowski/laravel-api-response-builder
```

To instll specific version, use

```bash
composer require marcin-orlowski/laravel-api-response-builder:<VERSION>
```

 where `<VERSION>` string consists of `MAJOR` and `MINOR` release numbers. For
 example if you want to use version 6.3, you need to invoke:

```bash
composer require marcin-orlowski/laravel-api-response-builder:6.3
```

 which will add  the dependency at the release 6.3 + all the bugfixing releses
 (`6.3.*`) but won't automatically pull 6.4 (nor newer) even if available, unless
 `composer.json` is updated manually.

 If you want to use different configuration than `ResponseBuilder` defaults,
 publish and edit configuration file as described in [Configuration file](config.md)
 documentation.

## Setup ##

 `ResponseBuilder` supports Laravel's auto-discovery feature and it's ready to use once
 installed.

# Configuration file #

 `ResponseBuilder` looks for `config/response_builder.php` [configuration file](../config/response_builder.php).
 It's advised to publish default config file to application's `config/` directory on instllation
 and then tweak it as needed: 
 
```bash
 php artisan vendor:publish
```

# Notes #

 > ![NOTE](img/notes.png) If you are going to use [Exception Handler Helper](exceptions.md), you **MUST** configure it
 > first in your config file (esp. `default` handler configuration)!
