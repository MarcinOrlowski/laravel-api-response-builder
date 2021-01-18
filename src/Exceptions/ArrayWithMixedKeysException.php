<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Exceptions;

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
final class ArrayWithMixedKeysException extends \Exception
{
	/** @var string */
	protected $message =
		'Invalid data array. Either set own keys for all the items or do not specify any keys at all. ' .
		'Arrays with mixed keys are not supported by design.';
}
