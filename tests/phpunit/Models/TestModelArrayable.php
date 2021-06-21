<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Models;

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

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Request;

/**
 * Class TestModel to verify auto-conversion feature
 */
class TestModelArrayable implements Arrayable
{
	/** @var string Name of $val attribute, referenced by tests to avoid hardcoding */
	public const FIELD_NAME = 'val';

	/** @var string|null */
	protected $val;

	/**
	 * TestModel constructor.
	 *
	 * @param string $val
	 */
	public function __construct(string $val)
	{
		$this->val = $val;
	}

	/**
	 * @return string|null
	 */
	public function getVal(): ?string
	{
		return $this->val;
	}

	/**
	 * Converts model to array.
	 *
	 * @param Request $request
	 *
	 * @return array
	 *
	 * @noinspection PhpUnusedParameterInspection
	 *
	 * NOTE: No typehint as signature must match JsonResource::toArray()
	 * @noinspection PhpMissingParamTypeInspection
	 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
	 */
	public function toArray($request = null): array
	{
		return [
			self::FIELD_NAME => $this->val,
		];
	}
}
