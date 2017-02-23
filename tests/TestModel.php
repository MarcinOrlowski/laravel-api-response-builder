<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2017 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */


/**
 * Class TestModel to verify auto-conversion feature
 */
class TestModel
{
	/** @var string|null */
	protected $val = null;

	/**
	 * TestModel constructor.
	 *
	 * @param string $val
	 */
	public function __construct($val)
	{
		$this->val = $val;
	}

	/**
	 * Converts model to array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return [
			'val' => $this->val,
		];
	}
}
