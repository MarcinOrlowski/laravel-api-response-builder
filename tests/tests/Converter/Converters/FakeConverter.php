<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Converter\Converters;

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

use MarcinOrlowski\ResponseBuilder\Contracts\ConverterContract;

/**
 * Class FakeConverter
 *
 * @package MarcinOrlowski\ResponseBuilder\Tests\Converters
 */
class FakeConverter implements ConverterContract
{
    public $key = 'fake';
    public $val = 'converter';

	/**
	 * Simulates object conversion.
	 *
	 * @param object $obj
	 * @param array  $config
	 *
	 * @return string[]
	 */
	public function convert($obj, /** @scrutinizer ignore-unused */ array $config): array
    {
        return [$this->key => $this->val];
    }
}
