# API Response Builder for Laravel #

 `ResponseBuilder` follows [Semantic Versioning](http://semver.org/).

### v4 ###

 * `ErrorCodes` class is now `BaseApiCodes`
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
