<?php

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */
//class TestCase extends Illuminate\Foundation\Testing\TestCase {
class TestCase extends Orchestra\Testbench\TestCase {


	protected function getPackageProviders($app)
	{
		return [
			MarcinOrlowski\ResponseBuilder\ResponseBuilderServiceProvider::class,
		];
	}

	protected function getPackageAliases($app)
	{
		return [
			'ResponseBuilder' => 'MarcinOrlowski\ResponseBuilder',
		];
	}


	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		parent::setUp();

		// Your code here
	}


	/**
	 * Creates the application.
	 *
	 * @return \Illuminate\Foundation\Application
	 */
	public function createApplication()
	{
		$app = require __DIR__ . '/bootstrap.php';

		$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

		return $app;
	}


	/**
	 * Tears down the app
	 */
	public function tearDown() {
		// restore all ENVs (if altered via putenv())
		$this->restoreEnvs();

		parent::tearDown();
	}

	//----[ putenv/getenv helpers ]---------------------------------------------------------------

	/**
	 * Env vars that existed when we putenv() it, so it shall be restored by restoreEnvs() when test is done
	 *
	 * @var array
	 */
	protected $envs_to_restore = [];

	/**
	 * Env vars that did NOT exist we putenv() it, so it shall be unset by restoreEnvs() when test is done
	 *
	 * @var array
	 */
	protected $envs_to_unset = [];

	/**
	 * Wraper for putenv() that stores previous values of env variable
	 * prior altering it, so restoreEnvs() can revert all the changes
	 * once test is done
	 *
	 * @param string $key key of the env variable to change
	 * @param mixed $val new value (optional). If ommited, env variable will be unset (removed)
	 *
	 * @return bool
	 */
	protected function putenv($key, $val=self::PUTENV_UNIQUE_DEFAULT_VALUE) {
		$old = getenv($key);
		if( $old !== false ) {
			$this->envs_to_restore[$key] = $old;
		} else {
			$this->envs_to_unset[$key] = $key;
		}

		if($val !== self::PUTENV_UNIQUE_DEFAULT_VALUE) {
			if( is_bool($val) ) {
				$val = ($val) ? 'true' : 'false';
			}
			return putenv("{$key}={$val}");
		} else {
			return putenv($key);
		}
	}

	/**
	 * Unique value used as defaults for putenv()'s value to allow passing @null
	 * and to support putenv()'s unset feature
	 */
	const PUTENV_UNIQUE_DEFAULT_VALUE = 'j5eGNsFZ4BfpZqeb!uxpFRMqt!1ZOjiaGTX';


	/**
	 * Wrapper for getenv(). Gets the value of an environment variable.
	 *
	 * @param string $key variable name
	 *
	 * @return string Returns the value of the environment variable varname, or FALSE if the environment variable varname does not exist
	 */
	protected function getenv($key) {
		return getenv($key);
	}

	/**
	 * Reverts all changes made to env variables via putenv() methods, by restoring previous
	 * values or unsetting the variable if it wasn't exist.
	 */
	protected function restoreEnvs() {
		foreach( $this->envs_to_unset as $key ) {
			putenv($key);
		}
		$this->envs_to_unset = [];

		foreach( $this->envs_to_restore as $key=>$val ) {
			putenv(sprintf('%s=%s', $key, $val));
		}
		$this->envs_to_restore = [];
	}

	//----[ end of putenv/getenv helpers ]--------------------------------------------------------



	/**
	 * Builds UploadedFile object with content of existing file
	 *
	 * @param string $file_name full path to existing file we want content of to be used
	 * @param string $orig_name file name of the file as it should be visible later on
	 *
	 * @return \Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	protected function buildFile($file_name, $orig_name='audio.mp4') {
		$temp_file = tempnam(sys_get_temp_dir(), str_random());
		copy(base_path($file_name), $temp_file);
		return new Symfony\Component\HttpFoundation\File\UploadedFile($temp_file, $orig_name, null, filesize($file_name), null, true);
	}

	/**
	 * Improved assertion method with message containing original error from backend
	 * and not only expected http status code.
	 */
	public function assertResponseOk() {
		$actual = $this->response->getStatusCode();

		$this->assertTrue($this->response->isOk(), "Expected status code 200, got {$actual}. Response: {$this->response->getContent()}");
	}

	/**
	 * Checks if Response's code matches our expectations. If not, shows ErrorCode::XXX constant name of expected and current values
	 * for easy debugging
	 *
	 * @param int $expected_code ErrorCodes::XXX code expected
	 * @param $response_json response json object
	 */
	public function assertResponseStatusCode($expected_code, $response_json) {
		$response_code = $response_json->code;

		if( $response_code != $expected_code ) {
			$msg = sprintf('Status code mismatch. Expected: %s, found %s. Message: "%s"',
				$this->resolveConstantFromCode($expected_code),
				$this->resolveConstantFromCode($response_code),
				$response_json->message);

			$this->fail($msg);
		}
	}

	/**
	 * Returns ErrorCode constant name referenced by its value
	 *
	 * @param $error_code
	 *
	 * @return int|null|string
	 */
	protected function resolveConstantFromCode($error_code) {
		$const = \App\ErrorCodes::getErrorCodeConstants();
		$name = null;
		foreach( $const as $const_name => $const_value ) {
			if( is_int($const_value) && ($const_value == $error_code) ) {
				$name = $const_name;
				break;
			}
		}

		return is_null($name) ? "??? ({$error_code})" : $name;
	}

	/**
	 * Helper to let test protected/private methods
	 *
	 * Usage example:
	 * ----------------
	 *   $method = $this->getProtectedMethod('App\Foo', 'someMethod');
	 *   $obj = new \App\Foo();
	 *   $result = $method->invokeArgs($obj, ...);
	 *
	 * @param string $class_name name of the class method belongs to, i.e. "Bar". Can be namespaced i.e. "Foo\Bar" (no starting backslash)
	 * @param string $method_name method name to call
	 *
	 * @return \ReflectionMethod
	 */
	public function getProtectedMethod($class_name, $method_name) {
		$class = new \ReflectionClass($class_name);
		$method = $class->getMethod($method_name);
		$method->setAccessible(true);

		return $method;
	}


	/**
	 * Checks if response structure (in common areas) matches design
	 *
	 * @param $json_object
	 */
	protected function validateCommonResponseStructure($json_object) {
		$fields = ['status','success','code','locale','message', 'data'];
		foreach( $fields as $field ) {
			$this->assertObjectHasAttribute($field, $json_object, "No '{$field}' in received response");
		}
		$this->assertEquals(\App::getLocale(), $json_object->locale, "Response locale mismatch");
		$this->assertTrue($json_object->message != '', "Message cannot be empty string");
	}

	/**
	 * Checks if response structure of failed operation matches design
	 *
	 * @param $json_string
	 */
	protected function validateErrorResponseStructure($json_string) {
		$json = json_decode($json_string);
		$this->assertNotNull($json, 'Failed to decode JSON response');

		$this->validateCommonResponseStructure($json);

		$expected = \App\Helpers\ResponseBuilder::STATUS_ERROR;
		$this->assertEquals($expected, $json->status, "Status string is not '{$expected}' as expected");
		$this->assertFalse($json->success);

		$this->assertNotEquals(\App\ErrorCodes::OK, $json->code, "Error code cannot equal OK code");
		$this->assertTrue(\App\ErrorCodes::isCodeValid($json->code), "Error code outside allowed range");
	}

	/**
	 * Checks if response structure of successful operation matches design
	 *
	 * @param $json_string_or_object
	 */
	protected function validateOkResponseStructure($json_string_or_object) {
		if( is_string($json_string_or_object)) {
			$json = json_decode($json_string_or_object);
		} else {
			if( is_object($json_string_or_object) ) {
				$json = $json_string_or_object;
			} else {
				throw new \RuntimeException("Invalid argument. Must be either JSON strong or object");
			}
		}
		$this->assertNotNull($json, 'Failed to decode JSON response');

		$this->validateCommonResponseStructure($json);
		$expected = \App\Helpers\ResponseBuilder::STATUS_OK;
		$this->assertEquals($expected, $json->status, "Status string is not '{$expected}' as expected");
		$this->assertTrue($json->success);
		$this->assertEquals(\App\ErrorCodes::OK, $json->code, "Status value not ErrorCodes::OK");

		$data = $json->data;
		$this->assertNotNull($data, "'data' node cannot be null");
	}

}
