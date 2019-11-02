![REST API Response Builder for Laravel](img/logo.png)

# REST API Response Builder for Laravel #

### v6 ###

#### v6.3 ####
 * `[BREAKING]` This is backward incompatible change in signature of `ResponseBuilder::buildResponse()`, but it only affects
   you if you extend `ResponseBuilder` and provide own implementation to manipulate response object
   (see [Manipulating Response Object](docs.md#manipulating-response-object)). If you do not, then you are not affected.

#### v6.2 ####
 * `[Very Low]` Data conversion logic changed slightly. Now it checks if we have configuration entry matching **exactly** the
  object's class name. If not, then we'd try to find if we have any configuration for its parent class.
  See [Data Conversion](docs.md#data-conversion) for details.

#### v6.1 ####
 * `[Very Low]` Removed ability to define own names for response keys which reduces code complexity and simplifies the
 library. From now one you need to stick to default names now (`success`, `code`, `message`, `locale`, `data`).

#### v6.0 ####

 * Requires Laravel 6.0+ and PHP 7.2+
 * `[BREAKING]` In previous versions built-in reserved codes were hardcoded and always in range of 1-63 which, in general
 contradicted the whole idea of having code ranges. Starting from v6, all your API codes are always within user assigned code range
 including built-in codes. This implies some breaking changes to the configuration of `ResponseBuilder` and some changes
 in the way built-in codes are handled. Because all built-in codes are now remapped to user defined code range, all built-in code
 constants like `OK` or `EX_HTTP_SERVICE_UNAVAILABLE`, previously defined in `BaseApiCodes` class are gone. If you for any reason
 need to get the value of built in code, use  what's the value You can still have If you need to get the value of
 built-in code you need to use replacement methods. These are named the same way as the constants, so if you want to get code 
 of `ApiCodes::OK` you need to call `ApiCodes::OK()` (or directly, `BaseApiCodes::OK()`). See `BaseApiCodes` class for all
 available public functions. Additionally, first 20 codes (0 to 19 inclusive) of your code range is reserved for built-in codes, 
 which means that if you define your code range to be `100`-`199` then you cannot use codes `100` to `119` for own purposes.
 The first code you can assign to own API code is `120`. 
 * `[Low]` Removed `exception_handler.use_exception_message_first` feature.
 * `[Low]` Removed `ResponseBuilder::DEFAULT_API_CODE_OK` constant.
 * `[Low]` Removed `getReservedMinCode()`, `getReservedMinCode()`, `getReservedMessageKey()` methods from `ApiCodesHelpers` trait.
 * `[Low]` All `ResponseBuilder` internal code constants are removed. If you need to get the valid API code for internal codes, 
 use `BaseApiCodes` class' methods: `OK()`, `NO_ERROR_MESSAGE()`, `EX_HTTP_NOT_FOUND()`, `EX_HTTP_SERVICE_UNAVAILABLE()`,
 `EX_HTTP_EXCEPTION()`, `EX_UNCAUGHT_EXCEPTION()`, `EX_AUTHENTICATION_EXCEPTION()` and `EX_VALIDATION_EXCEPTION()` that would
 return valid API code in currently configured range.
 * `[Low]` Removed `response_key_map` configuration option.
			
### v5 ###

 * No public release.


### v4 ###

 * `ApiCodeBase` class is now `BaseApiCodes`
 * ExceptionHandler's debug trace no longer depends on `APP_DEBUG` value and can be enabled independently


### v3 ###

 * `success()` now accepts (optional) `api_code` too, therefore signature of this method as well as and argument
 order changed. This makes it **partially** incompatible with what have been in v2, however in majority of uses
 this change should pose no threat at all. If you were just calling `success()` or `success($data)` (which is 
 99,9% of use cases) then you are all fine and no change is needed. But if you were setting own 
 `http_code` or `lang_args` when calling `success()` then you need to update your code. 
 * `:api_code` is now the code value placeholder in all the strings. `:error_code` is no longer supported
 * `ErrorCodes` class is now `ApiCodeBase`
 * ApiCodeBase's `getErrorCodeConstants()` is now `getApiCodeConstants()`
 * ApiCodeBase's `getMapping()` is now `getCodeMessageKey()`
 * ApiCodeBase's `getBaseMapping()` is now `getReservedCodeMessageKey()`
 * Internal constants for `ExceptionHandlerHelper` supported exceptions are now prefixed with `EX_` (i.e. `HTTP_NOT_FOUND`
 is now `EX_HTTP_NOT_FOUND`)


### v2 ###

 * First public release


### v1 ###

 * Initial (internal) release
