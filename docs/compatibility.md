# API Response Builder for Laravel 5 #

`ResponseBuilder` is Laravel5's helper designed to simplify building
nice, normalized and easy to consume REST API responses.

## Preface ##

 **NOTE:** You should **ONLY** worry about compatibility if you are upgrading from previous versions as you may
 be using APIs that changed. If you are new to the project and you just started using it, simply do not bother
 untill **next major update** (see [Semantic Versioning](http://semver.org/)).

## Compatibility ##

### v3.0 ###

 * Signature of `success()` changed as now it allows custom `api_code` to be returned along with the response order
 of method arguments has changed. In majority of cases this is not a problem but if you are returning custom 
 `http_code` or passing `lang_args` then you need to fix your code to work with this version.
 
### v2.x ###

 * No public API changes.

### v1.x ###

 * Initial (internal) release

## Changelog ##

 See [CHANGES.md](CHANGES.md) for detailed revision history.
