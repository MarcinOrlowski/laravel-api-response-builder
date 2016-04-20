# API Response Builder for Laravel 5 #

## CHANGE LOG ##

* v1.5.1 (2016-04-21)
   * Added errorWithMessageAndData()
   * Added env('EX_INCLUDE_CLASS_NAME') to control include_class_name exception handler feature
   * If app runs in DEBUG mode, ExceptionHandlerHelper now adds 'file' and 'line' to returned JSON

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
