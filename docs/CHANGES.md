![REST API Response Builder for Laravel](../artwork/laravel-api-response-builder-logo.svg)


# REST API Response Builder for Laravel #

 > ![WARNING](img/warning.png) This library follows [semantic versioning](https://semver.org).
 > See [compatibility docs](compatibility.md) for details about backward compatibility
 > **before** doing major upgrade!

## CHANGE LOG ##

* v9.3.0 (2021-06-22)
  * Added `data_always_object` config option that, when enabled enforces response `data` node
    to always be JSON object (for `NULL` it will return empty object `{}`).
  * Updated project logo
  * Updated code style to match standard ruleset.
  * Improved code quality (fully pass on PHPStan's strict mode)
  * Fixed floats being rejected as direct primitive payload.
  * Fixed `Converter` unit tests for primitives-as-payload.
  * Fixed `ResponseBuilderProvider` throwing incorrect Exception in case of invalid config file.
  * Added `Validator::assertIsObjectOrExistingClass()` method.
  * `Validator::assertIsInt()` throws now `NotIntegerException` as expected.
  * Corrected `Validator` class tests to check agains specific exceptions thrown, not base class.
  * Improved error handling in `JsonSerializableConverter`
  * Switched Composer's autoload to follow `psr-4` instead of plain `classmap` (thanks to Viktor Szépe).
  * Added `TestingHelpers::getResponseContent()`, `TestingHelpers::langGet()` to satisfy static analyzers.
  * Updated and corrected PHPDocs (incl. some type hints).
  * Added missing type hint to `success()`.
  * Added strict type header to classes.
  * Removed `dev` block from default `composer.json`. See `.config/README.md` for details.
  * Updated documentation.

* v9.2.3 (2021-04-21)
  * [RB-194] Changed signature of ExceptionHandlers' `handle()` method to expectc `Throwable`
    instead of `Exception` (reported by @genesiscz).

* v9.2.2 (2021-03-05)
   * [RB-190] Fixed converting resource and resource collection (reported by @achinkumar121).

* v9.2.1 (2021-01-18)
   * [RB-186] ExceptionHandler now expects `Throwable` instead of `Exception`.

* v9.2.0 (2020-12-27)
   * Updated Travis config to run tests on PHP 8 too.
   * Added Arabic translation (thanks to @mustafa-online)
   * Added Turkish translation (thanks to @victorioustr)

* v9.1.1 (2020-10-30)
   * Documentation and repository structure cleanup.

* v9.1.0 (2020-10-29)
   * [RB-175] `Paginator` and `LengthAwarePaginator` support is now included in default converter 
     configuration (reported by @kcaj-burr).
   * Fixed `testConfigClassesMappingEntriesUnwantedConfigKeys()` testing trait not supporting
     `null` keys in converter config.

* v9.0.3 (2020-10-27)
   * `Validator` type related exceptions must now implement `InvalidTypeExceptionContract`.
   * The `converter` config `key` element now accepts `null` to indicate you want no key to
     be used (patch by Raja).

* v9.0.2 (2020-10-24)
   * Corrected tests to use regular ServiceProvider.
   * Corrected primitive converter tests.
   * Presence of configuration "converter/classes" array is now mandatory (reported by Raja).
   * Extensive documentation overhaul.

* v9.0.1 (2020-10-22)
   * Fixed auto-discovery failing due to broken `ServiceProvider` (reported by Efriandika Pratama).
   * Corrected documentation and usage examples.

* v9.0.0 (2020-10-17)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](compatibility.md)).
   * [RB-156] Added logic to deal with directly returned objects or arrays.
   * [RB-158] Passing primitives as direct payload (i.e. `success(12.50);` is now supported for `array`, `boolean`,
     `double`, `integer` and `string` types, configurable via new `converter/primitives`.
   * Removed hadrcoded `val` key used by `JsonSerializable` converter.
   * Introduced own exceptions for better error reporting. See [src/Exceptions](../src/Exceptions) for more info.

* v8.1.1 (2020-10-15)
   * [RB-155] Fixed `ResponseBuilder` internals preventing exdending class code from
     being invoked, thus making response object structure manipulation ineffective (reported by @krek95).

* v8.1.0 (2020-09-17)
   * Added logging (`.env` controllable) for payload Converter to help debugging inproper data conversion.

* v8.0.1 (2020-09-09)
   * Updated Travis config to make tests run against Laravel 8.0.
   * Removed `Util::printArray()` helper.

* v8.0.0 (2020-07-14)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](compatibility.md)).
   * Improved performance by using calls qualified references.
   * [RB-132] Reworked exception handler helper to support delegated handlers for better flexibility.
   * Reverted depreciation of `BaseApiCodes` reserved range codes.
   * Sealed built-in data converter classes.
   * Removed `ResponseBuilderLegacy` class from the package.
   * Added German localization.

* v7.1.2 (2020-07-12)
   * [RB-141] Fixed `JsonSerializableConverter` to deal non-string return data (reported by Jonatan Fekete).

* v7.1.1 (2020-07-11)
   * Added more tests.
   * Updated dependencies.
   * Switched away from deprecated Codacy coverage package.

* v7.1.0 (2020-03-04)
   * Added support for Laravel v7.

* v7.0.3 (2019-12-30)
   * Fixed `composer.json` file.

* v7.0.2 (2019-12-29)
   * Updated Travis config to cover Laravel 6.5, 6.6, 6.7 and 6.8.
   * Updated Travis config to cover PHP 7.4.

* v7.0.1 (2019-11-25)
   * Disabled Scrutinizer's "false positive" in Builder class.
   * Added more tests to improve overall coverage.
   * Updated PHPDocs.

* v7.0.0 (2019-11-22)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](compatibility.md)).
   * New, flexible API based on `Builder` pattern (see [docs](compatibility.md) for details).
   * Reworked `ExceptionHandlerHelper` configuration. Now, you will be able to easily configure every `HttpException`
     for each HTTP status code you want. Separate `ExceptionHandler::TYPE_HTTP_NOT_FOUND_KEY` and all related stuff, 
     incl. localization key `http_not_found`, configuration is now replace with more flexible generic code that provides
     error messages for all supported HTTP codes from in range `400-599`.
   * Added support for external data converters (related part of config changed too).
   * Config key `classes` is now (partially) `converter`. Its `method` key is gone and `handler` is used instead.
     needs to be added now, pointing to the class implementing `ConverterContract` acting as delegate worker.
   * Data converter now handles objects implementing `JsonSerializable` and `Arrayable` contracts as well.

* v6.3.2 (2019-11-07)
   * Added `RB::successWithMessage()` method.
   * Entries in `classes` config array can now have `pri` to enforce order while merging config with a built-in configuration.
   * Persian translation (Thanks to @FaridAghili).
   * Added Laravel 6.5 to Travis-CI unit tests.

* v6.3.1 (2019-11-06)
   * Fixed config merging helper causing certain user settings to be lost.
   * [RB-107] No longer exposes exception class name for message-less exceptions.
   * Added test ensuring that user privided config overshadows built-in params.

* v6.3.0 (2019-11-02)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](compatibility.md))
   * Signature of `RB::buildResponse()` changed to allow customization of final `message` entry (@hawezo).
   * Moved all code that produces messages for API codes to `RB::getMessageForApiCode()`.
   * Added `Validator::assertType()` helper method that validates var against set of allowed types.
   * Added `Validator::assertString()` helper.

* v6.2.3 (2019-10-31)
   * Added Laravel 6.4 to Travis-CI unit tests.
   * Corrected example in "Manipulating Response Object" docs.

* v6.2.2 (2019-10-22)
   * Squashed multiple typographic errors in the documentation files.

* v6.2.1 (2019-10-21)
   * Added Laravel 6.3 to Travis-CI unit tests.
   * Split tests into separate folders per class tested.
   * ExceptionHandler no longer tries to enforce UTF-8 on exception message.
   * Added PHP 7.4-snapshot to unit tests.

* v6.2.0 (2019-10-19)
   * Changed how auto-converter checks for supported classes (see [Data Conversion](conversion.md))
   * Data conversion now supports [JsonResource](https://laravel.com/docs/6.0/eloquent-resources) data class.
   * Added unit test for `ResponseBuilderServiceProvider::mergeConfg()`.
   * Moved data conversion code to separate `Converter` class.
   * Added `LICENSE.md` file.
   * Added Laravel 6.2 to Travis-CI unit tests.
   * Added unit tests for translation files.

* v6.1.2 (2019-10-02)
   * Corrected ServiceProvider used for tests.

* v6.1.1 (2019-10-02)
   * Fixed `ResponseBuilderServiceProvider` using unreferenced `Arr` class method (reported by yassir3wad).

* v6.1.0 (2019-09-28)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](compatibility.md))
   * Created new library logo (see [artwork/](../artwork/) folder).
   * Added more unit tests to improve coverage.
   * Updated documentation.
   * Worked around Laravel's config merger not working properly with multi-dimensional config arrays.
   * Corrected ApiCodesTests trait failing on some methods.
   * Included ApiCodesTest trait in base tests to avoid de-sync in future releases.
   * Removed custom response keys mapping feature.

* v6.0.0 (2019-09-20)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](compatibility.md))
   * Requires Laravel 6.0+ and PHP 7.2+ (see [docs](legacy.md) for legacy support hints).
   * All API codes are now withing defined code range, incl. built-in codes.
   * Reserved codes reduced to 19 (from former 63).
   * Added type hints to all method arguments and return values
   * `ExceptionHandler` responses use exception specific HTTP code.
   * Fixed `RB::errorWithMessageAndData()` not passing data properly.
   * Fixed exception message thrown by `ApiCodesHelpers::getMaxCode()`.
   * Corrected test cases list in `testSuccess_DataAndHttpCode()`.
   * Fixed error code fallback in `testRender_HttpException()` test.
   * Fixed `testError_DebugTrace()` not containing any asserts.
   * Reformatted code to not exceed 132 columns, for better on-line readability.
   * `RB::errorWithDataAndHttpCode()` accepts now `null` as http code.
   * `RB::errorWithHttpCode()` accepts now `null` as http code.
   * Fixed `ExceptionHandlerHelper` replacing HTTP codes above 499 with 400.
   * Changed default message for `HTTP_NOT_FOUND` error.
   * `ExceptionHandler` now falls back to `EX_UNCAUGHT_EXCEPTION` for all the cases.
   * Simplified `ExceptionHandlerHelperTest::testRender_HttpException()` test.
   * Removed `exception_handler.use_exception_message_first` feature.
   * Removed `RB::DEFAULT_API_CODE_OK` constant.
   * Removed `getReservedMinCode()`, `getReservedMinCode()`, `getReservedMessageKey()` methods.
   * Removed internal API code constants. Use corresponding methods to get proper code value.
   * Reimplemented Laravel config merger to support multi-dimensional configuration arrays too.
   * Removed `response_key_map` configuration option.
   * You can now return HTTP codes from 5xx range with all error responses.

* v5.0.0
   * Skipped to catch up with Laravel version numeration.

* v4.1.9 (2019-08-08)
   * Fixed `ApiCodesHelpers::getMaxCode()` exception message.
   * Fixed `RB::errorWithMessageAndData()` not passing args properly.

* v4.1.8 (2019-08-07)
   * Added Laravel 6 to testing setup.

* v4.1.7 (2019-03-03)
   * Added PHP 7.3 to testing setup.
   * Added Laravel 5.7 and Laravel 5.8 to testing setup.
   * Corrected test env setup for Laravel 5.5, 5.6.
   * Removed tests on HHVM from Travis config.

* v4.1.6 (2018-07-20)
   * Documentation updated.

* v4.1.5 (2018-02-24)
   * Fixed `version` in `composer.json` file.

* v4.1.4 (2018-02-24)
   * Updated tests to run on PHP 5.6 too.
   * Corrected docs.

* v4.1.3 (2017-10-23)
   * Corrected docs.

* v4.1.2 (2017-09-10)
   * Corrected docs.

* v4.1.1 (2017-09-10)
   * Fixed `version` in `composer.json` file.

* v4.1.0 (2017-09-09)
   * `[RB-70]` Added support for Laravel 5.5's auto-discovery feature.

* v4.0.2 (2017-04-13)
   * Enforced HTTP code for error messages fits 400-499 range.
   * `validateResponseStructure()` deprecated in favor of `assertValidResponse()`.
   * Moved Orchestra's `getPackageProviders()` out of `TestingHelpers` trait.

* v4.0.1 (2017-04-10)
   * TestingHelpers trait's `validateResponseStructure()` method is now public.
   * `[RB-64]` Fixed Exception Handler generated HTTP code being out of allowed range in some cases.
   * `[RB-65]` Exception Handler Helper now deals with messages using non-UTF8 or broken encoding.
   * Exception Handler's trace data is now properly placed into `trace` leaf.

* v4.0.0 (2017-04-10)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](compatibility.md)).
   * `[RB-59]` Added option to remap response JSON keys to user provided values.
   * `[RB-54]` Debug data no longer pollutes `data` leaf. Instead, it adds `debug` dictionary to root data structure.
   * `[RB-37]` Added support for Laravel 5.3+ `unauthenticated()` in Exception Handler. See new config keys defaults.
   * `[RB-47]` Exception Handler now supports `FormRequests` and returns all messages in `RB::KEY_MESSAGES`.
   * Uncaught `HttpResponse::HTTP_UNAUTHORIZED` exception is now handled same way `authentication_exception` is.
   * `[RB-56]` Added configurable key for debug trace added to returned JSON response (if enabled).
   * Added traits to help testing your config and ApiCodes with ease. See `Unit Testing your ApiCodes` docs for details.
   * `ApiCodeBase` class is now named `BaseApiCodes`.
   * `[RB-35]` ExceptionHandlerHelper is now covered by tests.

* v3.2.1 (2017-04-06)
   * `[RB-49] Fixed `artisan vendor:publish` not publishing config file correctly.

* v3.2.0 (2017-03-02)
   * `[RB-42] Default value of `encoding_options` include `JSON_UNESCAPED_UNICODE` to prevent unicode escaping.
   * `[RB-41]` Updated documentation.

* v3.1.0 (2017-02-28)
   * `[RB-38] Added `encoding_options` to control data-to-json conversion.
   * `[RB-38] Added optional encoding options args to all methods accepting `data` argument.
   * `[RB-34]` Added option to control ExceptionHandler behavior on debug builds.
   * ExceptionHandler's debug is now added as `debug` node to make it more clear where it comes from.

* v3.0.3 (2017-02-24)
   * No changes. v3.0.2 was incorrectly released.

* v3.0.2 (2017-02-24)
   * `[RB-31] Fixed incorrect exception message thrown in case of incomplete `classes` config mapping (@dragonfire1119).

* v3.0.1 (2017-02-23)
   * Updated `composer.json` to list `laravel/framework` among requirements.

* v3.0.0 (2017-02-23)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](compatibility.md)).
   * `[RB-17] `success()` now allows to return API code as well.
   * Corrected default config file containing faulty and unneeded `use` entries.
   * `[RB-20]` Renamed ErrorCode class to ApiCodeBase.
   * ApiCodeBase's `getMinCode()` and `getMaxCode()` are now `public`.
   * Improved error messages to be even more informative.
   * All exceptions thrown due to misconfiguration have `CONFIG: ` message prefix now.
   * Renamed `error_code` param to `api_code` in all the method signatures.
   * `:api_code` is now code placeholder in strings (`:error_code` is no longer supported).
   * Default HTTP codes are now declared as constants `DEFAULT_HTTP_CODE_xxx` if you need to know them.
   * `ApiCodeBase::getMap()` now ensures `map` config entry of expected `array` type.
   * `[RB-26]` Added `successWithCode()` method.

* v2.2.1 (2017-02-20)
   * Documentation split into separate files.

* v2.2.0 (2017-02-20)
   * `[RB-5]` Fixed error code range not being checked when used with custom message strings.
   * `successWithHttpCode()`, `errorWithDataAndHttpCode()`, `errorWithHttpCode()` throws exception if `http_code` is `null`.
   * `http_code` can be handed as null to all other methods and it will be replaced by default codes.
   * `classes` mapping now features `method` field to specify method name to call for automatic object conversion.
   * `[RB-10] When `$data` is an `array`, all elements mapped via "classes" config will be converted recursively.
   * `[RB-3]` Unit tests are now part of the package.

* v2.1.2 (2016-08-24)
   * Fixed exception code handling in ExceptionHandlerHelper (reported by Adrian Chen @absszero)

* v2.1.1 (2016-08-23)
   * Fixed bad handling of HTTP error code in exception handler (reported by Adrian Chen @absszero)

* v2.1.0 (2016-05-16)
   * Eloquent Model can now be directly returned as response payload.
   * Eloquent Collection can now be directly returned as response payload.
   * Added some config parameters (see `config/response_builder.php` in `vendor/....`).
   * You can now pass literally anything to be returned in `data` payload, however data type conversion will be enforced
     to ensure returning data matches specification.
   * Updated documentation.

* v2.0.0 (2016-04-21)
   * Configuration file structure changed
   * Built-in localization keys changed
   * Added `errorWithMessageAndData()` method
   * ExceptionHandlerHelper adds `class`, `file` and `line` to returned JSON for apps in DEBUG mode
   * ExceptionHandlerHelper can now use `:message`, `:api_code`, `:http_code` and `:class` placeholders
   * ExceptionHandlerHelper now automatically resolves message mappings and needs no config entries
   * ExceptionHandlerHelper now comes with built-in error codes (still, using own codes is recommended)
   * Added option to configure HTTP codes for each `ExceptionHandlerHelper` returned response separately
   * Exception provided messages can now have priorities over ExceptionHandlerHelper configured error messages

* v1.5.0 (2016-04-18)
   * ExHandler: ExceptionHandler is now replaced by ExceptionHandlerHelper
   * ExHandler: Added option to omit Exception class name in emitted uncaught exception message

* v1.4.2 (2016-04-16)
   * Added chapter about manipulating response object
   * Code cleanup

* v1.4.1 (2016-04-14)
   * Removed pointless Handler's overloading of `report()`
   * Code style cleanup

* v1.4.0 (2016-04-12)
   * Replaced ErrorCodes class with ErrorCode, as it should be that way from the start

* v1.3.0 (2016-04-12)
   * Reworked Exception Handler making it even easier to use
   * Docs cleanup

* v1.2.0 (2016-04-12)
   * Fixed issue with messages not resolving properly
   * Incorporated Exception Handler's messages
   * Added Polish translation

* v1.1.0 (2016-04-12)
   * Corrected issue with `data` returned as empty object not null
   * Changed fallback error message
   * Expanded docs with more examples
   * Changed internal codes and mappings

* v1.0.1 (2016-04-11)
   * Docs cleanup
   * Added `extras/` with ready to use exception handler

* v1.0.0 (2016-04-11)
   * Initial public release

