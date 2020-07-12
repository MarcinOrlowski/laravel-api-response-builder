![REST API Response Builder for Laravel](img/logo.png)

# Legacy support #

 Starting from version 6.0, `ResponseBuilder` requires [Laravel](https://laravel.com/) v6 or newer which automatically means it
 also requires PHP 7.2+, same as Laravel. 
 
 It however does not mean, recent `RespnseBuilder` will not work with older Laravel versions (and even lower PHP versions)
 but this is no longer officially supported. No unit tests are executed against anything older than Laravel 6.x and PHP 7.2,
 so if for any reasons you want to use new `ResponseBuilder` with legacy environment then you are on your own. At least ensure
 all unit tests pass.
 
 If you cannot upgrade for any reason, you can still use legacy `ResponseBuilder` v4, which supports Laravel 5.1+ and 
 PHP 5.1 and up:
 
     composer require marcin-orlowski/laravel-api-response-builder "^4.0" 
  
 Alternativel add dependency manually, by editing `composer.json` manually and add following line to `require` section:

    "require": {
       "marcin-orlowski/laravel-api-response-builder": "^4.0",
      ...
    }
