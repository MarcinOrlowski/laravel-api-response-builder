<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class MakeTest extends TestCase
{
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function testWrongMessage(): void
	{
		$this->expectException(\InvalidArgumentException::class);

		/** @var \MarcinOrlowski\ResponseBuilder\BaseApiCodes $api_codes_class_name */
		$api_codes_class_name = $this->getApiCodesClassName();

		$message_or_api_code = [];    // invalid data type

		/** @noinspection PhpUnhandledExceptionInspection */
		/** @noinspection PhpParamsInspection */
		$this->callMakeMethod(true, $api_codes_class_name::OK(), $message_or_api_code);
	}

	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function testCustomMessageAndCodeOutOfRange(): void
	{
		$this->expectException(\InvalidArgumentException::class);

		$api_code = $this->max_allowed_code + 1;    // invalid
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->callMakeMethod(true, $api_code, 'message');
	}
}
