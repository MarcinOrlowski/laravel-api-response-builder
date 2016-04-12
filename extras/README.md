# Exception Handler with Response Builder #
 
Properly designed API shall never hit consumer with HTML nor anything like that. While in regular use this
is quite easy to achieve, unexpected problems like uncaught exception or even enabled maintenance mode
can confuse many APIs world wide. Do not be one of them and take care of that too. With Laravel this
can be achieved with custom Exception Handler and Response Builder's built-in Exception Handler will
take care of everything out-of-the-box, returning proper JSON responses even in case of unexpected
difficulties.


## Here's how to do that? ##

Edit your `ErrorCodes` class and add the following constants, **assigning unique codes within your allowed
code range**:
 
    const RESPONSE_BUILDER_UNKNOWN_METHOD = ...;
    const RESPONSE_BUILDER_SERVICE_IN_MAINTENANCE = ...;
    const RESPONSE_BUILDER_HTTP_EXCEPTION = ...;
    const RESPONSE_BUILDER_UNCAUGHT_EXCEPTION = ...;

Edit `config/response_builder.php` file and register above codes in `map` array:

	ErrorCodes::RESPONSE_BUILDER_UNKNOWN_METHOD         => 'response-builder::builder.unknown_method',
	ErrorCodes::RESPONSE_BUILDER_SERVICE_IN_MAINTENANCE => 'response-builder::builder.service_in_maintenance',
	ErrorCodes::RESPONSE_BUILDER_HTTP_EXCEPTION         => 'response-builder::builder.http_exception_fmt',
	ErrorCodes::RESPONSE_BUILDER_UNCAUGHT_EXCEPTION     => 'response-builder::builder.uncaught_exception_fmt',
	
In the same file edit `exception_handler` entry to make it look like this:

	'exception_handler' => [
		'unknown_method'         => ErrorCodes::RESPONSE_BUILDER_UNKNOWN_METHOD,
		'service_in_maintenance' => ErrorCodes::RESPONSE_BUILDER_SERVICE_IN_MAINTENANCE,
		'http_exception'         => ErrorCodes::RESPONSE_BUILDER_HTTP_EXCEPTION,
		'uncaught_exception'     => ErrorCodes::RESPONSE_BUILDER_UNCAUGHT_EXCEPTION,
	],
    
Finally edit `app/Exceptions/Handler.php` file and replace **all** its content with:

    <?php
    namespace App\Exceptions;
    
    use MarcinOrlowski\ResponseBuilder\ResponseBuilderExceptionHandler as ExceptionHandler;
    
    class Handler extends ExceptionHandler {}


## Using own messages ##

The above links codes with Response Builder built-in messages, but you can use any strings you want. For 
`RESPONSE_BUILDER_UNCAUGHT_EXCEPTION` and `RESPONSE_BUILDER_HTTP_EXCEPTION` codes `:message` can be used
which will be substituted by actual exception message.


## Notes ##

The above assumes you keep your codes in `ErrorCodes` class stored in `app/ErrorCodes.php` and using `App\ErrorCodes` namespace. 
If  you keep them elsewhere, then you need to edit provided `Handler.php` file and replace `App\ErrorCodes` with right namespace
in the line:

    use App\ErrorCodes as ResponseBuilderErrorCodes;

(the `as ResponseBuilderErrorCodes` must remain unaltered). Also edit `use` line in `config/response_builder.php` to for 
use proper namespace.
