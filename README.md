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

 * [Introduction](#introduction)
 * [Why should I use it?](#benefits)
 * [Usage examples](docs/examples.md#usage-examples)
 * [Features](#features)
 * [Extensive documentation](docs/README.md)
 * [License](#license)
 * [Changelog](docs/CHANGES.md)

----

## Introduction ##

 `ResponseBuilder` is a [Laravel](https://laravel.com/) package, designed to help you build a nice, normalized and easy to consume
 REST API JSON responses.

## Benefits ##

 `ResponseBuilder` is written for REST API developers by REST API developer and is based on long-lasting experience on both
 "sides" of API. It's lightweight, extensively tested, simple to use yet flexible and powerful, with support for on-the-fly
 data conversion, localization, automatic message building, chained APIs and (hopefully) exhaustive documentation.
 But that's not all! The JSON structure produced by `ResponseBuilder` is designed with **users of your API** in mind,
 to make dealing with your API a breeze. Simple JSON response, with well-defined and predictable structure, easy to
 consume without any hassle or trickery.

 You are even covered in a case of emergency, as provided Exception Handler helper, ensures your API keeps talking JSON (and
 not HTML) to its clients even in case of unexpected.

 Did I mention, you would also get testing traits that automatically unit test your whole `ResponseBuilder` related code
 and configuration with just a few lines of code?

## Features ##

 * [Easy to use](docs/README.md#usage-examples),
 * [Stable and production ready](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder),
 * [On-the-fly data object conversion](docs/conversion.md),
 * API chaining support,
 * [Localization support](docs/docs.md#messages-and-localization),
 * Provides traits to help [unit test your API code](docs/testing.md),
 * Comes with [exception handler helper](docs/exceptions.md) to ensure your API stays consumable even in case of unexpected,
 * No additional dependencies.


## License ##

 * Written and copyrighted &copy;2016-2021 by Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * ResponseBuilder is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
