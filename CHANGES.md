![REST API Response Builder for Laravel](docs/logo.png)

# REST API Response Builder for Laravel #

See [compatibility docs](docs/compatibility.md) for details about backward compatibility!

## CHANGE LOG ##

* 6.1.0 (2019-09-28)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](docs/compatibility.md))
   * Created new library logo (see `artwork/` folder)
   * Added more unit tests to improve coverage.
   * Updated documentation.
   * Worked arround Laravel's config merger wnot working properly with multi-dimensional config arrays.
   * Corrected ApiCodesTests trait failing on some methods.
   * Included ApiCodesTest trait in base tests to avoid desync in future releases.
   * Removed custom response keys mapping feature.

* v6.0.0 (2019-09-20)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](docs/compatibility.md))
   * Requires Laravel 6.0+ and PHP 7.2+ (see docs for legacy support hints).
   * All API codes are now withing defined code range, incl. built-in codes.
   * Reserved code range lowered from former 63 down to 19.
   * Added type hints to all method arguments and return values
   * `ExceptionHandler` returns Exception-type related HTTP codes now as per configuration.
   * Fixed `ResponseBuilder::errorWithMessageAndData()` not passing data properly.
   * Fixed exception message of `ApiCodesHelpers::getMaxCode()`.
   * Corrected test cases list in `testSuccess_DataAndHttpCode()`.
   * Fixed error code fallback in `testRender_HttpException()` test.
   * Fixed `testError_DebugTrace()` not containing any asserts.
   * Reformatted code to not exceed 132 columns, for better on-line readability.
   * `ResponseBuilder::errorWithDataAndHttpCode()` accepts now `null` as http code.
   * `ResponseBuilder::errorWithHttpCode()` accepts now `null` as http code.
   * Fixed `ExceptionHandlerHelper` replacing HTTP codes above 499 with 400.
   * Changed default built-in message for `HTTP_NOT_FOUND` error.
   * `ExceptionHandler` now falls back to `EX_UNCAUGHT_EXCEPTION` for all the cases.
   * Simplified `ExceptionHandlerHelperTest::testRender_HttpException()` test.
   * Removed `exception_handler.use_exception_message_first` feature.
   * Removed `ResponseBuilder::DEFAULT_API_CODE_OK` constant.
   * Removed `getReservedMinCode()`, `getReservedMinCode()`, `getReservedMessageKey()` methods.
   * Removed internal API code constants. Use corresponding methods to get proprt code value.
   * Reimplemented Laravel config merger to support multi-dimensional configuration arrays too.
   * Removed `response_key_map` configuration option. 
   * You can now return HTTP codes from 5xx range with all error responses. 

* v5.0.0
   * Skipped to catch up with Laravel version numeration.

* v4.1.9 (2019-08-08)
   * Fixed `ApiCodesHelpers::getMaxCode()` exception message
   * Fixed `ResponseBuilder::errorWithMessageAndData()` not passing args properly

* v4.1.8 (2019-08-07)
   * Added Laravel 6 to testing setup

* v4.1.7 (2019-03-03)
   * Added PHP 7.3 to testing setup
   * Added Laravel 5.7 and Laravel 5.8 to testing setup
   * Corrected test env setup for Laravel 5.5, 5.6
   * Removed tests on HHVM from Travis config  

* v4.1.6 (2018-07-20)
   * Documentation updated

* v4.1.5 (2018-02-24)
   * Fixed `version` in `composer.json` file

* v4.1.4 (2018-02-24)
   * Updated tests to run on PHP 5.6 too
   * Corrected docs

* v4.1.3 (2017-10-23)
   * Corrected docs

* v4.1.2 (2017-09-10)
   * Corrected docs

* v4.1.1 (2017-09-10)
   * Fixed `version` in `composer.json` file

* v4.1.0 (2017-09-09)
   * [RB-70] Added support for Laravel 5.5's auto-discovery feature

* v4.0.2 (2017-04-13)
   * Enforced HTTP code for error messages fits 400-499 range
   * `validateResponseStructure()` deprecated in favor of `assertValidResponse()`
   * Moved Orchestra's `getPackageProviders()` out of `TestingHelpers` trait

* v4.0.1 (2017-04-10)
   * TestingHelpers trait's `validateResponseStructure()` method is now public
   * [RB-64] Fixed Exception Handler generated HTTP code being out of allowed range in some cases
   * [RB-65] Exception Handler Helper now deals with messages using non-UTF8 or broken encoding
   * Exception Handler's trace data is now properly placed into `trace` leaf

* v4.0.0 (2017-04-10)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](docs/compatibility.md))
   * [RB-59] Added option to remap response JSON keys to user provided values
   * [RB-54] Debug data no longer pollutes `data` leaf. Instead, it adds `debug` dictionary to root data structure.
   * [RB-37] Added support for Laravel 5.3+ `unauthenticated()` in Exception Handler. See new config keys defails
   * [RB-47] Exception Handler now supports `FormRequests` and returns all messages in `ResponseBuilder::KEY_MESSAGES`
   * Uncaught `HttpResponse::HTTP_UNAUTHORIZED` exception is now handled same way `authentication_exception` is
   * [RB-56] Added configurable key for debug trace added to returned JSON response (if enabled)
   * Added traits to help testing your config and ApiCodes with ease. See `Unit Testing your ApiCodes` docs for details
   * `ApiCodeBase` class is now named `BaseApiCodes`
   * [RB-35] ExceptionHandlerHelper is now covered by tests

* v3.2.1 (2017-04-06)
   * [RB-49] Fixed `artisan vendor:publish` not publishing config file correctly

* v3.2.0 (2017-03-02)
   * [RB-42] Default value of `encoding_options` include `JSON_UNESCAPED_UNICODE` to prevent unicode escaping
   * [RB-41] Updated documentation

* v3.1.0 (2017-02-28)
   * [RB-38] Added `encoding_options` to control data-to-json conversion.
   * [RB-38] Added optional encoding options args to all methods accepting `data` argument
   * [RB-34] Added option to control ExceptionHandler behavior on debug builds
   * ExceptionHandler's debug is now added as `debug` node to make it more clear where it comes from

* v3.0.3 (2017-02-24)
   * No changes. v3.0.2 was incorrectly released

* v3.0.2 (2017-02-24)
   * [RB-31] Fixed incorrect exception message thrown in case of incomplete `classes` config mapping (@dragonfire1119)

* v3.0.1 (2017-02-23)
   * Updated `composer.json` to list `laravel/framework` among requirements

* v3.0.0 (2017-02-23)
   * **BACKWARD INCOMPATIBLE CHANGES** ([more info](docs/compatibility.md))
   * [RB-17] `success()` now allows to return API code as well
   * Corrected default config file containing faulty and unneeded `use` entries
   * [RB-20] Renamed ErrorCode class to ApiCodeBase
   * ApiCodeBase's `getMinCode()` and `getMaxCode()` are now `public`
   * Improved error messages to be even more informative
   * All exceptions thrown due to misconfiguration have `CONFIG: ` message prefix now
   * Renamed `error_code` param to `api_code` in all the method signatures
   * `:api_code` is now code placeholder in strings (`:error_code` is no longer supported)
   * Default HTTP codes are now declared as constants `DEFAULT_HTTP_CODE_xxx` if you need to know them
   * `ApiCodeBase::getMap()` now ensures `map` config entry of expected `array` type
   * [RB-26] Added `successWithCode()` method

* v2.2.1 (2017-02-20)
   * Documentation split into separate files

* v2.2.0 (2017-02-20)
   * [RB-5] Fixed error code range not being checked when used with custom message strings
   * `successWithHttpCode()`, `errorWithDataAndHttpCode()`, `errorWithHttpCode()` throws exception if `http_code` is `null`
   * `http_code` can be handed as null to all other methods and it will be replaced by default codes
   * `classes` mapping now features `method` field to specify method name to call for automatic object conversion
   * [RB-10] When `$data` is an `array`, all elements mapped via "classes" config will be converted recursively
   * [RB-3] Unit tests are now part of the package

* v2.1.2 (2016-08-24)
   * Fixed exception code handling in ExceptionHandlerHelper (reported by Adrian Chen @absszero)

* v2.1.1 (2016-08-23)
   * Fixed bad handling of HTTP error code in exception handler (reported by Adrian Chen @absszero)

* v2.1.0 (2016-05-16)
   * Eloquent Model can now be directly returned as response payload.
   * Eloquent Collection can now be directly returned as response payload.
   * Added some config parameters (see `config/response_builder.php` in `vendor/....`)
   * You can now pass literally anything to be returned in `data` payload, however data type conversion will be enforced to ensure returning data matches specification
   * Updated documentation

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

