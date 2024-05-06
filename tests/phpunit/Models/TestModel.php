<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Models;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
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

	protected ?string $val;

	/**
	 * TestModel constructor.
	 */
	public function __construct(string $val)
	{
		$this->val = $val;
	}

	public function getVal(): ?string
	{
		return $this->val;
	}

	/**
	 * Converts model to array
	 */
	public function toArray(): array
	{
		return [
			self::FIELD_NAME => $this->val,
		];
	}

} // end of class
