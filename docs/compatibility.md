![REST API Response Builder for Laravel](img/laravel-logolockup-rgb-red.png)

# REST API Response Builder for Laravel #

 `ResponseBuilder` follows [Semantic Versioning](http://semver.org/).


### v6 ###

 * Requires Laravel 6.0+ and PHP 7.2+
 * [BREAK] In previous versions built-in reserved codes were hardcoded and always in range of 1-63 which somehow, in certain
 situations contradicted the idea of code ranges. Starting from v6, all API codes (incl. built-in) are always within user
 assigned code range. This implies some breaking changes to the configuration of `ResponseBuilder`. Your API codes are no longer
 defined as fixed value (i.e. `const SOMETHING_WENT_WRONG = 172;`) but as **offset** from your range starting value (`min_code`).
 Default range starts at 100, so the above constant should now be `const SOMETHING_WENT_WRONG = 72;` (hint: you can write this as
 `const SOMETHING_WENT_WRONG = +72;` which may improve code readability). Please note that code value must be lower than defined
 `max_code` therefore pay attention if you are going to change your ranges. If i.e. you'd change your range to be 100-150, then
 `SOMETHING_WENT_WRONG` would require new code assignment as `100+72 > 150`. Also note that first 19 values in your range
 (and also `OK` value of `0`) are reserved, therefore the lowest offset you can use is `20`.
 * [Low] Changed default HTTP code associated with each exception handled by `ExceptionHandler`. With no custom settings it will
 now return different HTTP code for different exception handled, while previously implementation could always return 
 `HTTP_BAD_REQUEST`. All users running on default settings, however, unless you client apps are HTTP code sensitive, the impact
 of this change is very low. Additionally, if you already set `http_code` field in your config (in ` exception_handler` block)
 then you need to change it from final value to **offset** as mentioned above.
 * [Low] Removed `exception_handler.use_exception_message_first` feature.


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
 * Internal constants for `ExeceptionHandlerHelper` supported exceptions are now prefixed with `EX_` (i.e. `HTTP_NOT_FOUND`
 is now `EX_HTTP_NOT_FOUND`)


### v2 ###

 * First public release


### v1 ###

 * Initial (internal) release
