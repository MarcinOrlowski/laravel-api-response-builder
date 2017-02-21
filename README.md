# API Response Builder for Laravel 5 #

`ResponseBuilder` is Laravel5's helper designed to simplify building
nice, normalized and easy to consume REST API responses.

[![Latest Stable Version](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/v/stable)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)
[![Build Status](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder.svg?branch=master)](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/44f427e872e2480597bde0242417a2a7)](https://www.codacy.com/app/MarcinOrlowski/laravel-api-response-builder?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=MarcinOrlowski/laravel-api-response-builder&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/44f427e872e2480597bde0242417a2a7)](https://www.codacy.com/app/MarcinOrlowski/laravel-api-response-builder?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=MarcinOrlowski/laravel-api-response-builder&amp;utm_campaign=Badge_Coverage)
[![Monthly Downloads](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/d/monthly)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)
[![License](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/license)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5c5f4dc1-41d5-49f9-b4ba-6268aa3fea00/big.png)](https://insight.sensiolabs.com/projects/5c5f4dc1-41d5-49f9-b4ba-6268aa3fea00)

[![Latest Unstable Version](https://poser.pugx.org/marcin-orlowski/laravel-api-response-builder/v/unstable)](https://packagist.org/packages/marcin-orlowski/laravel-api-response-builder)
[![Build Status](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder.svg?branch=dev)](https://travis-ci.org/MarcinOrlowski/laravel-api-response-builder)

## Table of contents ##

 **Upgrading from previous version? Ensure you read [compatibility docs](docs/compatibility.md) prior altering your `composer.json`!**
 
 * [Features](#features)
 * [Documentation](docs/docs.md)
 * [Bugs reports and pull requests](#contributing)
 * [License](#license)
 * [Changelog](#changelog)

----

## Donations ##

`ResponseBuilder` is free software (see [License](#license)) and you can use it fully free of charge in any of your projects, open source or 
commercial, however if you feel it prevent you from reinventing the wheel, helped having your projects done or simply saved you time and money 
then then feel free to donate to the project. Send some Bitcoins (BTC) to `1LbfbmZ1KfSNNTGAEHtP63h7FPDEPTa3Yo`.

![BTC](http://i.imgur.com/mUe8olT.png)

Thanks for all the fish!

----

## Features ##

 * Supports Laravel 5.1 and newer
 * Easy to use
 * Configurable (with ready-to-use defaults)
 * Localization support
 * Automated object conversion with custom mapping
 * Code ranges to support cascaded APIs
 * Built-in exception handler to ensure your API stays consumable even in case of unexpected
 * No extra dependencies and low requirements
 * Stable, production ready. 

----

## Contributing ##

Please report any issue spotted using [GitHub's project tracker](https://github.com/MarcinOrlowski/laravel-api-response-builder/issues).
 
If you'd like to contribute to the this project, please [open new ticket](https://github.com/MarcinOrlowski/laravel-api-response-builder/issues) 
**before doing any work**. This will help us save your
time in case I'd not be able to accept such changes. But if all is good and clear then follow common routine:

 * fork the project
 * create new branch
 * do your changes
 * send pull request

Thanks in advance!

----

## License ##

* Written and copyrighted &copy;2016-2017 by Marcin Orlowski <mail (#) marcinorlowski (.) com>
* ResponseBuilder is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

----

## Notes ##

* `ResponseBuilder` is **not** compatible with Lumen framework, mainly due to lack of Lang class. If you would like to help making `ResponseBuilder` usable with Lumen, speak up or (better) send pull request!

----

## Changelog ##

 See [CHANGES.md](CHANGES.md) for detailed revision history.
