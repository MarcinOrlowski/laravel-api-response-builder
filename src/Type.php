<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

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

/**
 * Data type constants
 */
final class Type
{
	/** @var string */
	public const STRING = 'string';

	/** @var string */
	public const INTEGER = 'integer';

	/** @var string */
	public const BOOLEAN = 'boolean';

	/** @var string */
	public const ARRAY   = 'array';

	/** @var string */
	public const OBJECT = 'object';

	/** @var string */
	public const DOUBLE = 'double';

	/** @var string */
	public const NULL = 'NULL';

	/** @var string */
	public const EXISTING_CLASS = 'existing class';
}
