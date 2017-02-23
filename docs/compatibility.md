# API Response Builder for Laravel 5 #

 `ResponseBuilder` follows [Semantic Versioning](http://semver.org/).

 You need to worry about backward compatibility if you are upgrading from previous major version of the package.

### v3 ###

 * `success()` now accepts (optional) `api_code` too, therefore signature of this method as well as and argument
 order changed. This makes it **partially** incompatible with what have been in v2, however in majority of uses
 this change should pose no threat at all. If you were just calling `success()` or `success($data)` (which is 
 99,9% of use cases) then you are all fine and no change is needed. But if you were setting own 
 `http_code` or `lang_args` when calling `success()` then you need to update your code. 
 * `:api_code` is now the code value placeholder in all the strings. `:error_code` is no longer supported
 * ApiCodeBase's `getErrorCodeConstants()` is now `getApiCogideConstants()`
 * ApiCodeBase's `getMapping()` is now `getCodeMessageKey()`
 * ApiCodeBase's `getBaseMapping()` is now `getReservedCodeMessageKey()`

### v2 ###

 * First public release.

### v1 ###

 * Initial (internal) release
