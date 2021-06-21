![REST API Response Builder for Laravel](../artwork/laravel-api-response-builder-logo.svg)

# REST API Response Builder for Laravel #

`ResponseBuilder` is a helper library for [Laravel](https://laravel.com/) framework,
designed to help you build nice, normalized and easy to consume JSON response structures
for your REST API.

## Test dependencies ##

Development dependencies are no longer part of default `composer.json` as they differ
depending on target platform and Laravel versions. All dedicated text files sit
in `.config/` folder. To install dependencies based on that file use:

```bash
$ COMPOSER=.config/composer-laravel-8.x.json composer install
```

then you can run all the tests as usual.
