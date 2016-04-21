# API Response Builder for Laravel 5 #

## CHANGE LOG ##

* v2.0.0 (2016-04-21)
   * Configuration file structure changed!
   * Built-in localization keys changed
   * Added errorWithMessageAndData() method
   * If app runs in DEBUG mode, ExceptionHandlerHelper will add 'file' and 'line' to returned JSON
   * ExceptionHandlerHelper now automatically resolves message mappings and needs no config entries
   * ExceptionHandlerHelper now comes with built-in error codes (still, using own codes isrecommended)
   * Added option to configure HTTP codes for each ExceptionHandlerHelper returned response separately

* v1.5.0 (2016-04-18)
   * ExHandler: ExceptionHandler is now replaced by ExceptionHandlerHelper
   * ExHandler: Added option to ommit Exception class name in emited uncaught exception message

* v1.4.2 (2016-04-16)
   * Added chapter about manipulating response object
   * Code cleanup

* v1.4.1 (2016-04-14)
   * Removed pointles Handler's overloading to report()
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
   * Added extras/ with ready to use exception handler

* v1.0.0 (2016-04-11)
   * Initial public release
