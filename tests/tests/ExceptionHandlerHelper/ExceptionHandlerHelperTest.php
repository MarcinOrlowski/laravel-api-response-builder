<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2021 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlerHelper;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlers\DefaultExceptionHandler;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionHandlerHelperTest extends TestCase
{
    /**
     * Tests behaviour of ExceptionHandler::unauthenticated()
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testUnauthenticated(): void
    {
        $exception = new AuthenticationException();

        $obj = new ExceptionHandlerHelper();
        $eh_response = $this->callProtectedMethod($obj, 'unauthenticated', [null,
                                                                            $exception]);

        $response = json_decode($eh_response->getContent(), false);

        $this->assertValidResponse($response);
        $this->assertNull($response->data);
        $this->assertEquals(BaseApiCodes::EX_AUTHENTICATION_EXCEPTION(), $response->{RB::KEY_CODE});
        $this->assertEquals($exception->getMessage(), $response->{RB::KEY_MESSAGE});
    }

    /**
     * Tests if optional debug info is properly added to JSON response
     *
     * @return void
     */
    public function testErrorMethodWithDebugTrace(): void
    {
        /** @noinspection PhpUndefinedClassInspection */
        \Config::set(RB::CONF_KEY_DEBUG_EX_TRACE_ENABLED, true);

        $exception = new \RuntimeException();

        $j = json_decode(ExceptionHandlerHelper::render(null, $exception)->getContent(), false);
        $this->assertValidResponse($j);
        $this->assertNull($j->data);

        $key = RB::KEY_DEBUG;
        $this->assertObjectHasAttribute($key, $j, "No '{key}' element in response structure found");

        // Note that we do not check what debug node contains. It's on purpose as whatever ends up there
        // is not generated by us, so may change at any time.
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Check exception handler behavior when provided with various exception types.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testRenderMethodWithHttpException(): void
    {
        $codes = [
            [
                'exception_class'       => ValidationException::class,
                'expected_http_code'    => HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
                'expected_api_code'     => BaseApiCodes::EX_VALIDATION_EXCEPTION(),
                'do_message_validation' => false,
                'has_data'              => true,
            ],
        ];

        foreach ($codes as $exception_type => $params) {
            $this->doTestSingleException((string)($exception_type), $params['exception_class'],
                $params['expected_http_code'], $params['expected_api_code'],
                $params['do_message_validation'], $params['has_data']);
        }
    }

    /**
     * Handles single exception testing.
     *
     * @param string $exception_config_key           ResponseBuilder's config key for this particular exception.
     * @param string $exception_class                Name of the class of exception to be constructed.
     * @param int    $expected_http_code             Expected response HTTP code
     * @param int    $expected_api_code              Expected response API code
     * @param bool   $validate_response_message_text Set to @true, to validate returned response message with
     *                                               current localization.
     * @param bool   $expect_data                    Set to @true if response is expected to have non null `data` node.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @noinspection PhpTooManyParametersInspection
     */
    protected function doTestSingleException(string $exception_config_key,
                                             string $exception_class,
                                             int $expected_http_code, int $expected_api_code,
                                             bool $validate_response_message_text = true,
                                             bool $expect_data = false): void
    {
        $key = BaseApiCodes::getCodeMessageKey($expected_api_code);
        $expect_data_node_null = true;
        switch ($exception_class) {
            case HttpException::class:
                $exception = new $exception_class($expected_http_code);
                break;

            case ValidationException::class:
                $data = ['title' => ''];
                $rules = ['title' => 'required|min:10|max:255'];
                /** @noinspection PhpUnhandledExceptionInspection */
                $validator = app('validator')->make($data, $rules);
                $exception = new ValidationException($validator);
                $expect_data_node_null = false;
                break;

            default:
                $exception = new $exception_class(null, $expected_http_code);
                break;
        }

        // hand the exception to the handler and examine its response JSON
        $eh_response = ExceptionHandlerHelper::render(null, $exception);
        $eh_response_json = json_decode($eh_response->getContent(), false);

        $this->assertValidResponse($eh_response_json);
        if ($expect_data_node_null) {
            $this->assertNull($eh_response_json->data);
        }

        $ex_message = trim($exception->getMessage());
        if ($ex_message === '') {
            $ex_message = '???';
        }

        /** @noinspection PhpUndefinedClassInspection */
        $error_message = \Lang::get($key, [
	        'response_api_code' => $expected_api_code,
	        'message'           => $ex_message,
	        'class'             => \get_class($exception),
        ]);

        if ($validate_response_message_text) {
            $this->assertEquals($error_message, $eh_response_json->message);
        }
        $this->assertEquals($expected_http_code, $eh_response->getStatusCode(),
            sprintf('Unexpected HTTP code value for "%s".', $exception_config_key));
        if ($expect_data) {
            $data = $eh_response_json->{RB::KEY_DATA};
            $this->assertNotNull($data);
            $this->assertObjectHasAttribute(RB::KEY_MESSAGES, $data);
            $this->assertIsObject($data->{RB::KEY_MESSAGES});
        }
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Tests if ExceptionHandler's error() method will correctly drop invalid HTTP
     * found in configuration, and try to obtain code from the exception.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testHttpCodeFallbackToExceptionStatusCode(): void
    {
        // GIVEN invalid configuration with exception handler's http_code set
        // to value below min. allowed 400
        $config_http_code = HttpResponse::HTTP_OK;

        // AND having HttpException with valid http_code
        $expected_http_code = HttpResponse::HTTP_BAD_REQUEST;
        $ex = new HttpException($expected_http_code);

        // THEN we should get valid response with $expected_http_code used.
        $this->doTestErrorMethodFallbackMechanism($expected_http_code, $ex, $config_http_code);
    }

    /**
     * Checks if error() will fall back to provided HTTP code, given the fact exception
     * handler configuration uses invalid `http_code` but also Exception's http status
     * code is set to invalid value. In such case we should fallback to DEFAULT_HTTP_CODE_ERROR.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testHttpCodeFallbackToProvidedFallbackValue(): void
    {
        // http codes below 400 are invalid
        $config_http_code = HttpResponse::HTTP_OK;
        $expected_http_code = RB::DEFAULT_HTTP_CODE_ERROR;

        $ex = new HttpException(HttpResponse::HTTP_OK);
        $this->doTestErrorMethodFallbackMechanism($expected_http_code, $ex, $config_http_code);
    }

    /**
     * Checks if Exception Handler would successfuly provide error message for valid HttpExceptions that
     * do not have dedicated error message configured.
     *
     * @throws \ReflectionException
     */
    public function testDefaultExceptionMessages(): void
    {
        // get the translation array for default language
        $translation = $this->getTranslationForDefaultLang();

//        for ($code = RB::ERROR_HTTP_CODE_MIN; $code <= RB::ERROR_HTTP_CODE_MAX; $code++) {
        {
            $code = 401;
            $key = "http_{$code}";
            // there are some gaps in the codes defined, but as default language  covers all codes supported,
            // then we can safely skip the codes not covered by default language.
            if (\array_key_exists($key, $translation)) {
                $ex = new HttpException($code);
                $response = $this->callProtectedMethod(ExceptionHandlerHelper::class, 'render', [
                        null,
                        $ex,
                    ]
                );

                // get response as Json object
                $json = json_decode($response->getContent(), false);
                $this->assertValidResponse($json);

                // Ensure returned response used HTTP code from the exception
                $this->assertNotEmpty($json->message);
                $this->assertEquals($translation[ $key ], $json->message,
                    "error message mismatch for http code: {$code}");
            }
        }
    }

    /**
     * Tests if Exception Handler's default (built-in) configuration matches structure requrements.
     *
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testBaseConfigStructure(): void
    {
        $cfg = $this->getExceptionHandlerConfig();
	    $keys = [
		    HttpException::class,
		    RB::KEY_DEFAULT,
	    ];
        $this->assertArrayHasKeys($keys, $cfg);

        // check http_exception block and validate all required entries and the config content.
        $http_cfg = $cfg[ HttpException::class ][RB::KEY_CONFIG];
        $this->assertGreaterThanOrEqual(1, \count($http_cfg));
        $keys = [HttpResponse::HTTP_UNAUTHORIZED,];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $http_cfg);
            $this->checkExceptionHandlerConfigEntryStructure($http_cfg[ $key ], null, ($key === 'default'));
        }
        $this->assertArrayHasKey(RB::KEY_DEFAULT, $http_cfg);
        $this->checkExceptionHandlerConfigEntryStructure($http_cfg[RB::KEY_DEFAULT]);

        // check default handler config
        $this->checkExceptionHandlerConfigEntryStructure($cfg[RB::KEY_DEFAULT][RB::KEY_CONFIG]);
    }

    /**
     * Validates ExceptionHandler's built-in configuration related to HttpException class.
     *
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testBaseConfigHttpExceptionConfig(): void
    {
        $http_cfg = $this->getExceptionHandlerConfig();
        $cfg = $http_cfg[ HttpException::class ][RB::KEY_CONFIG];

        foreach ($cfg as $code => $params) {
            if (\is_int($code)) {
                $this->checkExceptionHandlerConfigEntryStructure($params, $code);
            } elseif (\is_string($code) && $code == 'default') {
                $this->checkExceptionHandlerConfigEntryStructure($params, null, true);
            } else {
                $this->fail("Code '{$code}' is not allowed in config->exception_handler->http_exception.");
            }
        }
    }

    /**
     * Checks if ExceptionHandler would return exception's message if exists but fall
     * back to `msg_key` ignoring built-in default string
     *
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testExceptionMessageOverrideExceptionMessageOnly(): void
    {
        // HAVING exception handler configured to use user provided message string
        $api_code = BaseApiCodes::EX_HTTP_NOT_FOUND();
        $http_code = HttpResponse::HTTP_SERVICE_UNAVAILABLE;
        $msg_key = $this->getRandomString('key');
        $cfg = [
                RB::KEY_DEFAULT => [
	                RB::KEY_HANDLER => DefaultExceptionHandler::class,
	                RB::KEY_CONFIG  => [
		                RB::KEY_API_CODE  => $api_code,
		                RB::KEY_HTTP_CODE => $http_code,
		                RB::KEY_MSG_KEY   => $msg_key,
		                RB::KEY_MSG_FORCE => false,
	                ],
            ],
        ];
        Config::set(RB::CONF_KEY_EXCEPTION_HANDLER, $cfg);

        // GIVEN exception with message that should be handled
        $ex_msg = $this->getRandomString('user_msg');
        $ex = new \RuntimeException($ex_msg);

        $response = $this->callProtectedMethod(ExceptionHandlerHelper::class, 'render', [null,
                                                                                         $ex]);
        $json = json_decode($response->getContent(), false);
        $this->assertValidResponse($json);

        // THEN we should see exception message.

        // however thre's no message matching $msg_key, but Lang::get() would return
        // the key if no string exists, which is sufficient
        $this->assertEquals($ex_msg, $json->message);

        $this->assertEquals($http_code, $response->getStatusCode());
        $this->assertEquals($api_code, $json->code);
    }


    /**
     * Checks if ExceptionHandler would ignore exception's message as well as built-in fallback message
     * and use the one configured with `msg_key` instead.
     *
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testExceptionMessageForceOverride(): void
    {
        // HAVING exception handler configured to use user provided message string
        $api_code = BaseApiCodes::EX_HTTP_NOT_FOUND();
        $http_code = HttpResponse::HTTP_SERVICE_UNAVAILABLE;
        $msg_key = $this->getRandomString('key');
        $cfg = [
            'map' => [
                'default' => [
                    'api_code'  => $api_code,
                    'http_code' => $http_code,
                    'msg_key'   => $msg_key,
                    'msg_force' => true,
                ],
            ],
        ];
        Config::set(RB::CONF_KEY_EXCEPTION_HANDLER, $cfg);

        // GIVEN exception that should be handled
        $ex = new \RuntimeException('this message should be ignored');

        $response = $this->callProtectedMethod(ExceptionHandlerHelper::class, 'error', [
                $ex,
                $api_code,
                $http_code,
                $msg_key,
            ]
        );

        // get response as Json object
        $json = json_decode($response->getContent(), false);
        $this->assertValidResponse($json);

        // however thre's no message matching $msg_key, but Lang::get() would return
        // the key if no string exists, which is sufficient
        $this->assertEquals($msg_key, $json->message);

        $this->assertEquals($http_code, $response->getStatusCode());
        $this->assertEquals($api_code, $json->code);
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Checks if processException() would properly handle the case when there's no `msg_key` specified in exception
     * handler config for this particular exception type, yet method is ordered to ignore message provided by
     * exception and fall back one from config (which in this case means another fallback to built-in settings).
     *
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testProcessExceptionWithMsgEnforceWithNoFallbackMsgKey(): void
    {
        $api_code = mt_rand($this->min_allowed_code, $this->max_allowed_code);
        $http_code = mt_rand(RB::ERROR_HTTP_CODE_MIN, RB::ERROR_HTTP_CODE_MAX);
        do {
            $fallback_http_code = mt_rand(RB::ERROR_HTTP_CODE_MIN, RB::ERROR_HTTP_CODE_MAX);
        } while ($fallback_http_code === $http_code);

        $ex_cfg = [
            'api_code'    => $api_code,
            'http_code'   => $http_code,
            'msg_enforce' => true,
        ];

        $ex_msg = $this->getRandomString('ex');
        $ex = new \RuntimeException($ex_msg);

        /** @var HttpResponse $response */
        $response = $this->callProtectedMethod(ExceptionHandlerHelper::class, 'processException', [
            $ex,
            $ex_cfg,
            $fallback_http_code,
        ]);
        $json = json_decode($response->getContent(), false);
        $this->assertValidResponse($json);

        $msg = $ex->getMessage();
        $placeholders = [
            'api_code' => $api_code,
            'message'  => ($msg !== '') ? $msg : '???',
        ];
        $expected_msg_key = $this->callProtectedMethod(ExceptionHandlerHelper::class, 'getErrorMessageForException', [
            $ex,
            $http_code,
            $placeholders]);
        $expected_msg = \Lang::get($expected_msg_key, $placeholders);

        $this->assertEquals($expected_msg, $json->message);
        $this->assertEquals($http_code, $response->getStatusCode());
        $this->assertEquals($api_code, $json->code);
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Performs tests to ensure error() fallback mechanism for HTTP codes works correctly.
     *
     * @param int           $expected_http_code Expected HTTP code to be returned in response.
     * @param HttpException $ex                 Exception to use for testing.
     * @param int           $config_http_code   HTTP code to set as part for exception handler configuration
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    protected function doTestErrorMethodFallbackMechanism(int $expected_http_code,
                                                          HttpException $ex, int $config_http_code): void
    {
        // HAVING incorrectly configured exception handler
        $cfg = [
            'map' => [
                HttpException::class => [
                    HttpResponse::HTTP_NOT_FOUND => [
                        // i.e. OK (0) is invalid code for error response.
                        'api_code'  => BaseApiCodes::EX_HTTP_NOT_FOUND(),
                        'http_code' => $config_http_code,
                    ],
                ],
            ],
        ];
        Config::set(RB::CONF_KEY_EXCEPTION_HANDLER, $cfg);

        $response = $this->callProtectedMethod(ExceptionHandlerHelper::class, 'error', [
                $ex,
                BaseApiCodes::EX_HTTP_NOT_FOUND(),
                $config_http_code,
                '',
            ]
        );

        // get response as Json object
        $json = json_decode($response->getContent(), false);
        $this->assertValidResponse($json);

        // Ensure returned response used HTTP code from the exception
        $this->assertEquals($expected_http_code, $response->getStatusCode());
    }

    /**
     * Returns content of localization file for 'default' language.
     *
     * @return array
     */
    protected function getTranslationForDefaultLang(): array
    {
        // get the translation array for default language
        $default_lang = 'en';
        \App::setLocale($default_lang);

        return \Lang::get('response-builder::builder');
    }

    /**
     * Returns ExceptionHandler's configuration array.
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function getExceptionHandlerConfig(): array
    {
        $cfg = $this->callProtectedMethod(ExceptionHandlerHelper::class, 'getExceptionHandlerConfig', []);
        $this->assertIsArray($cfg);
        $this->assertNotEmpty($cfg);

        return $cfg;
    }

    /**
     * @param array    $params
     * @param int|null $code
     * @param bool     $is_default_handler
     *
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function checkExceptionHandlerConfigEntryStructure(array $params, ?int $code = null,
                                                                 bool $is_default_handler = false): void
    {
        if (\is_int($code)) {
            $this->assertGreaterThanOrEqual(RB::ERROR_HTTP_CODE_MIN, $code);
            $this->assertLessThanOrEqual(RB::ERROR_HTTP_CODE_MAX, $code);
        }

        if ($is_default_handler) {
            $mandatory_keys = [
                'api_code',
                'http_code',
            ];
            $optional_keys = [
                'pri',
                'msg_key',
                'msg_force'];
        } else {
            $mandatory_keys = [
                'api_code',
            ];
            $optional_keys = [
                'http_code',
                'pri',
                'msg_key',
                'msg_force',
            ];
        }

        $this->assertArrayHasKeys($mandatory_keys, $params);

        $this->assertIsInt($params['api_code']);
        $this->assertGreaterThanOrEqual(BaseApiCodes::getMinCode(), $params['api_code']);
        $this->assertLessThanOrEqual(BaseApiCodes::getMaxCode(), $params['api_code']);

        if (\array_key_exists('http_code', $params)) {
            $this->assertIsInt($params['http_code']);
            $this->assertGreaterThanOrEqual(RB::ERROR_HTTP_CODE_MIN, $params['http_code']);
            $this->assertLessThanOrEqual(RB::ERROR_HTTP_CODE_MAX, $params['http_code']);
        }

        // check config does not contain any unknown keys
        $diff = [];
        $allowed_keys = array_merge($mandatory_keys, $optional_keys);
        foreach ($params as $key => $val) {
            if (!\in_array($key, $allowed_keys)) {
                $diff[] = $key;
            }
        }

        $sep = "\n  ";
        $code_name = $code ?? '"default"';
        $msg = "Unsupported keys in config for HTTP Exception, handler for code {$code_name}:${sep}" . implode($sep, $diff);
        $this->assertEmpty($diff, $msg);
    }

}
