![REST API Response Builder for Laravel](docs/img/logo.png)

# REST API Response Builder for Laravel #

[![Latest Stable Version](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/v/stable)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)
[![Build Status](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder.svg?branch=master)](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/MarcinOrlowski/laravel-api-response-builder/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/MarcinOrlowski/laravel-api-response-builder/?branch=master)
[![Codacy Grade Badge](https://api.codacy.com/project/badge/Grade/44f427e872e2480597bde0242417a2a7)](https://www.codacy.com/app/MarcinOrlowski/laravel-api-response-builder?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=MarcinOrlowski/laravel-api-response-builder&amp;utm_campaign=Badge_Grade)
[![Monthly Downloads](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/d/monthly)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)
[![License](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/license)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5c5f4dc1-41d5-49f9-b4ba-6268aa3fea00/big.png)](https://insight.sensiolabs.com/projects/5c5f4dc1-41d5-49f9-b4ba-6268aa3fea00)

[![Latest Unstable Version](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/v/unstable)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)
[![Build Status](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder.svg?branch=dev)](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/MarcinOrlowski/laravel-api-response-builder/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/MarcinOrlowski/laravel-api-response-builder/?branch=dev)

## Table of contents ##

 * [Intriduction](#introduction)
 * [Why should I use it?](#benefits)
 * [Usage examples](#usage-examples)
 * [Features](#features)
 * [Documentation](docs/docs.md)
 * [Requirements](docs/docs.md#requirements)
 * [Installation and Configuration](docs/docs.md#installation-and-configuration)
 * [Bugs reports and pull requests](CONTRIBUTING.md)
 * [License](#license)
 * [Changelog](CHANGES.md)

 **Upgrading from previous version? Ensure you read [compatibility docs](docs/compatibility.md) prior altering your `composer.json`!**

----

## Introduction ##

 `ResponseBuilder` is [Laravel](https://laravel.com/)'s helper designed to simplify building  nice, normalized and standarized
 and easy to consume REST API JSON responses.

## Benefits ##

 `ResponseBuilder` is written for REST API developers by REST API developer and is based on my long lasting experiencde on both
 "sides" (API dev and API consumer) of variety of REST APIs. Lightweight, with simple to use public methods, covering multiple 
 potential use-cases, on-the-fly data conversion, localization support, automatic error message building, support
 for chainged APIs and (hopefuly) exhaustive documentation. But that's not all! The JSON structure produced by `ResponseBuilder` 
 is desinged with **users of your API** in mind, which helps them easily deal with your API with ease. They get simple, well
 defined and predictable JSON structure responses with all the fields needed to consume it without any unnecessary a hassle nor 
 other trickery. 
 
 Android developers can use [ApiResponse](https://github.com/MarcinOrlowski/ApiResponse) library to handle `ResponseBuilder` 
 responses produced in their mobile applications.   
 
 You are even covered in case of emergency as provided Exception Handler ensures your API keeps talking JSON (and 
 not HTML) to its clients if case of any unexpected and unhandled exception.
 
 Did I mention, you also get testing traits that would automatically cover your whole `ResponseBuilder` related code with 
 unit tests with just a few lines of code?

## Usage examples ##
 
 Operation successful? Conclude your controller method with:

    return ResponseBuilder::success();

 and your client will get nice JSON like

    {
      "success": true,
      "code": 0,
      "locale": "en",
      "message": "OK",
      "data": null
    }

 Something went wrong? Just do:

    return ResponseBuilder::error(250);

 and the following JSON response will be returned

    {
       "success": false,
       "code": 250,
       "locale": "en",
       "message": "Your error message for code 250",
       "data": null
    }

 Nice and easy! And yes, `message` can be easily customized! Also there're **much, much more** you can do with
 rich `ResponseBuilder` API. See [library documentation](docs/docs.md) for details and more examples!

----

## Features ##

 * Easy to use,
 * [Stable and production ready](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder),
 * Laravel compatibility: v6.0, v6.2 (see [legacy support](docs/legacy.md) for support for older versions),
 * Supports Laravel [auto-discovery](https://medium.com/@taylorotwell/package-auto-discovery-in-laravel-5-5-ea9e3ab20518),
 * Configurable (with ready-to-use defaults),
 * Localization support,
 * Automatic object conversion with custom mapping,
 * API chaining/cascading support,
 * Includes traits to help [unit testing your API code](docs/testing.md),
 * Provides own [exception handler helper](docs/exceptions.md) to ensure your API stays consumable even in case of unexpected,
 * No extra dependencies.

----

## License ##

 * Written and copyrighted &copy;2016-2019 by Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * ResponseBuilder is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

