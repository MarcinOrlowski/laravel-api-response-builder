# API Response Builder for Laravel 5 #

`ResponseBuilder` is Laravel5's helper designed to simplify building
nice, normalized and easy to consume REST API responses.

## Preface ##

 **NOTE:** You should **ONLY** worry about compatibility if you are upgrading from previous versions as you may
 be using APIs that changed. If you are new to the project and you just started using it, simply do not bother
 untill **next major update** (see [Semantic Versioning](http://semver.org/)).

## Compatibility ##

### v3.0 ###

 * `success()` changed to allow returning custom `api_code` with response, therefore signature of this method
  (and argument order) is different which makes it **partially** incompatible. In majority of uses this is not
  a problem but if you were calling `success()` passing `http_code` or `lang_args` to it, then you need to 
  update your code to work properly with 3.0.0. But if you were just calling `success()` or `success($data)` 
  (which is 99,9% of use cases) then you are all fine and no change is needed. 
 
### v2.x ###

 * No public API changes.

### v1.x ###

 * Initial (internal) release

## Changelog ##

 See [CHANGES.md](CHANGES.md) for detailed revision history.
