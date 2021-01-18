<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Exceptions;

use MarcinOrlowski\ResponseBuilder\Contracts\InvalidTypeExceptionContract;

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
class InvalidTypeException extends \Exception implements InvalidTypeExceptionContract
{
	/**
	 * NotAnTypeBaseException constructor.
	 *
	 * @param string       $var_name      Name of the variable (to be included in error message)
	 * @param string|array $allowed_types Array of allowed types [Type::*]
	 * @param string       $type          Current type of the $value
	 */
	public function __construct(string $var_name, string $type, array $allowed_types)
	{
		switch (\count($allowed_types)) {
			case 0:
				throw new \InvalidArgumentException('allowed_types array must not be empty.');
			case 1:
				$msg = '"%s" must be %s but %s found.';
				break;
			default;
				$msg = '"%s" must be one of allowed types: %s but %s found.';
				break;
		}

		parent::__construct(\sprintf($msg, $var_name, implode(', ', $allowed_types), $type));
	}

}
