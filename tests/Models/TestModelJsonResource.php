<?php

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

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Request;

/**
 * Class TestModel to verify auto-conversion feature
 */
class TestModelJsonResource extends JsonResource
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
     * Converts model to array. Signature must match JsonResource::toArray()
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request = null): array
    {
        return [
        	self::FIELD_NAME => $this->val,
        ];
    }
}
