<?php

namespace MarcinOrlowski\ResponseBuilder\Tests\Models;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2020 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

/**
 * Class TestModel to verify auto-conversion feature
 */
class TestModel
{
	/** @var string Name of $val attribute, referenced by tests to avoid hardcoding */
	public const FIELD_NAME = 'val';

	/**
	 * TestModel constructor.
	 *
	 * @param string $val
	 */
	public function __construct(string $val)
	{
		$this->{self::FIELD_NAME} = $val;
	}

	/**
	 * @return string|null
	 */
	public function getVal(): ?string
	{
		return $this->{self::FIELD_NAME};
	}

	/**
	 * Converts model to array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			self::FIELD_NAME =>
				$this->{self::FIELD_NAME},
		];
	}
}
