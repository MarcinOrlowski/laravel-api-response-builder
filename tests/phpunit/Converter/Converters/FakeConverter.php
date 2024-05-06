<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Converter\Converters;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\Contracts\ConverterContract;

/**
 * Class FakeConverter
 */
class FakeConverter implements ConverterContract
{
	/** @var string */
	public $key = 'fake';
	/** @var string */
	public $val = 'converter';

	/**
	 * Simulates object conversion.
	 *
	 * @param object $obj
	 * @param array  $config
	 *
	 * @return string[]
	 *
	 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceAfterLastUsed
	 */
	public function convert(object $obj, array $config): array
	{
		return [$this->key => $this->val];
	}

} // end of class
