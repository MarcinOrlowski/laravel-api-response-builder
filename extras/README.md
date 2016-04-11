# Exception Handler with Response Builder #
 
Properly designed API shall never hit consumer with HTML nor anything like that. While in regular use this
is quite easy to achieve, unexpected problems like uncaught exception or even enabled maintenance mode
can confuse many APIs world wide. Do not be one of them and take care of that too. With Laravel this
can be achieved with custom Exception Handler. 

Here's how to do that:

Edit your `ErrorCodes` class and add the following constants, assigning unique codes withing your code range:
 
    const RESPONSE_BUILDER_UNCAUGHT_EXCEPTION = ...;
    const RESPONSE_BUILDER_UNKNOWN_METHOD = ...;
    const RESPONSE_BUILDER_HTTP_EXCEPTION = ...;
    const RESPONSE_BUILDER_SERVICE_IN_MAINTENANCE = ...;

Then create `exception_handler.php` file in `app/resources/lang/en` directory, and add this there:
 
    <?php
    return [
       'uncaught_exception_fmt'  => 'Uncaught exception: :message',
       'unknown_method'          => 'Unknown method.',
       'http_exception_fmt'      => 'HTTP exception: :message',
       'service_in_maintenance'  => 'Service unavailable. Maintenance in progress.',
    ];
 
Next edit `config/response-builder.php` config file and add following lines to `map`:
 
    ErrorCodes::RESPONSE_BUILDER_UNCAUGHT_EXCEPTION     => 'exception_handler.uncaught_exception_fmt',
    ErrorCodes::RESPONSE_BUILDER_UNKNOWN_METHOD         => 'exception_handler.unknown_method',
    ErrorCodes::RESPONSE_BUILDER_HTTP_EXCEPTION         => 'exception_handler.http_exception_fmt',
    ErrorCodes::RESPONSE_BUILDER_SERVICE_IN_MAINTENANCE => 'exception_handler.service_in_maintenance',

Finally copy `Handler.php` file from `vendor/marcin-orlowski/laravel-api-response-builder/extras/` folder to your `app/Exceptions/` folder, 
overwriting existing handler


## Notes ##

The above assumes you keep your codes in `ErrorCodes` class stored in `app/ErrorCodes.php` and using `App\ErrorCodes` namespace. If you keep them
elsewhere, then you need to edit provided Handler class code and replace `App\ErrorCodes` with right namespace in the line:

    use App\ErrorCodes as ResponseBuilderErrorCodes;
