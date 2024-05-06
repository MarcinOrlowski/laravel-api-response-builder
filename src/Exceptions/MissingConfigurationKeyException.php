<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Exceptions;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */
final class MissingConfigurationKeyException extends ConfigurationException
{
	/**
	 * MissingConfigurationKeyException constructor.
	 *
	 * @param string $var_name
	 */
	public function __construct($var_name)
	{
		parent::__construct(sprintf('Missing "%s" key.', $var_name));
	}
}
