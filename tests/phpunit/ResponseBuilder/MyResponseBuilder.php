<?php
/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\ResponseBuilder;

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
class MyResponseBuilder extends \MarcinOrlowski\ResponseBuilder\ResponseBuilder
{
	/** @var array */
	public static $fake_response = [];

	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @param bool       $success
	 * @param int        $api_code
	 * @param int|string $msg_or_api_code
	 * @param array|null $placeholders
	 * @param null       $data
	 * @param array|null $debug_data
	 *
	 * @return array
	 *
	 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed
	 */
	protected function buildResponse(bool $success, int $api_code,
	                                 $msg_or_api_code, array $placeholders = null,
	                                 $data = null, array $debug_data = null): array
	{
		return static::$fake_response;
	}
}
