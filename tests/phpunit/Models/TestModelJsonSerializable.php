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

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Request;

/**
 * Class TestModel to verify auto-conversion feature
 */
class TestModelJsonSerializable implements \JsonSerializable
{
	/** @var string */
    protected $val;

	/**
	 * TestModelJsonSerializable constructor.
	 *
	 * @param mixed $val
	 *
	 * NOTE: no argument typehint due to compatibility with interface's signature.
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function __construct($val)
	{
		$this->val = $val;
	}

	/**
	 * @return string
	 *
	 * NOTE: no return typehint due to compatibility with Laravel signature.
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
    public function getVal()
    {
        return $this->val;
    }

	/**
	 * @return string
	 *
	 * NOTE: no typehints due to compatibility with interface's method signature.
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function jsonSerialize()
	{
		return $this->val;
	}
}
