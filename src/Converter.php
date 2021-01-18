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

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

/**
 * Data converter
 */
class Converter
{
	/** @var array */
	protected $classes = [];

	/** @var array */
	protected $primitives = [];

	/** @var bool */
	protected $debug_enabled = false;

	/**
	 * Converter constructor.
	 */
	public function __construct()
	{
		$this->classes = static::getClassesMapping() ?? [];
		$this->primitives = static::getPrimitivesMapping() ?? [];

		$this->debug_enabled = Config::get(RB::CONF_KEY_DEBUG_CONVERTER_DEBUG_ENABLED, false);
	}

	/**
	 * Returns "converter/primitives" entry for given primitive object or throws exception if no config found.
	 * Throws \RuntimeException if there's no config "classes" mapping entry for this object configured.
	 * Throws \InvalidArgumentException if No data conversion mapping configured for given class.
	 *
	 * @param boolean|string|double|array|int $data Primitive to get config for.
	 *
	 * @return array
	 *
	 * @throws Ex\ConfigurationNotFoundException
	 */
	protected function getPrimitiveMappingConfigOrThrow($data): array
	{
		$result = null;

		$type = \gettype($data);
		$result = $this->primitives[ $type ] ?? null;

		if ($result === null) {
			throw new Ex\ConfigurationNotFoundException(
				sprintf('No data conversion mapping configured for "%s" primitive.', $type));
		}

		if ($this->debug_enabled) {
			Log::debug(__CLASS__ . ": Converting primitive type of '{$type}' to data node with key '{$result[RB::KEY_KEY]}'.");
		}

		return $result;
	}

	/**
	 * Returns "converter/map" mapping configured for given $data object class or throws exception if not found.
	 *
	 * @param object $data Object to get config for.
	 *
	 * @return array
	 *
	 * @throws Ex\ConfigurationNotFoundException
	 */
	protected function getClassMappingConfigOrThrow(object $data): array
	{
		$result = null;
		$debug_result = '';

		// check for exact class name match...
		$cls = \get_class($data);
		if ($cls !== false) {
			if (\array_key_exists($cls, $this->classes)) {
				$result = $this->classes[ $cls ];
				$debug_result = 'exact config match';
			} else {
				// no exact match, then lets try with `instanceof`
				foreach (\array_keys($this->classes) as $class_name) {
					if ($data instanceof $class_name) {
						$result = $this->classes[ $class_name ];
						$debug_result = "subclass of {$class_name}";
						break;
					}
				}
			}
		}

		if ($result === null) {
			throw new Ex\ConfigurationNotFoundException(
				sprintf('No data conversion mapping configured for "%s" class.', $cls));
		}

		if ($this->debug_enabled) {
			Log::debug(__CLASS__ . ": Converting {$cls} using {$result[RB::KEY_HANDLER]} because: {$debug_result}.");
		}

		return $result;
	}

	/**
	 * Main entry for data conversion
	 *
	 * @param mixed|null $data
	 *
	 * @return array|null
	 */
	public function convert($data = null): ?array
	{
		if ($data === null) {
			return null;
		}

		$result = null;

		Validator::assertIsType('data', $data, [
			Type::ARRAY,
			Type::BOOLEAN,
			Type::DOUBLE,
			Type::INTEGER,
			Type::OBJECT,
			Type::STRING,
		]);

		if ($result === null && \is_object($data)) {
			$cfg = $this->getClassMappingConfigOrThrow($data);
			$worker = new $cfg[ RB::KEY_HANDLER ]();
			$result = $worker->convert($data, $cfg);
			$result = $cfg[ RB::KEY_KEY ] === null ? $result : [$cfg[ RB::KEY_KEY ] => $result];
        }

		if ($result === null && \is_array($data)) {
			$cfg = $this->getPrimitiveMappingConfigOrThrow($data);

			$result = $this->convertArray($data);
			if (!Util::isArrayWithNonNumericKeys($data)) {
				$result = [$cfg[ RB::KEY_KEY ] => $result];
			}
		}

		if (\is_bool($data) || \is_float($data) || \is_int($data) || \is_string($data)) {
			$result = [$this->getPrimitiveMappingConfigOrThrow($data)[ RB::KEY_KEY ] => $data];
		}

		return $result;
	}

	/**
	 * Recursively walks $data array and converts all known objects if found. Note
	 * $data array is passed by reference so source $data array may be modified.
	 *
	 * @param array $data array to recursively convert known elements of
	 *
	 * @return array
	 */
	protected function convertArray(array $data): array
	{
		Validator::assertArrayHasNoMixedKeys($data);

		foreach ($data as $key => $val) {
			if (\is_array($val)) {
				$data[ $key ] = $this->convertArray($val);
			} elseif (\is_object($val)) {
				$cfg = $this->getClassMappingConfigOrThrow($val);
				$worker = new $cfg[ RB::KEY_HANDLER ]();
				$converted_data = $worker->convert($val, $cfg);
				$data[ $key ] = $converted_data;
			}
		}

		return $data;
	}

	/**
	 * Reads and validates "converter/map" config mapping
	 *
	 * @return array Classes mapping as specified in configuration or empty array if configuration found
	 *
	 * @throws Ex\InvalidConfigurationException if whole config mapping is technically invalid (i.e. not an array etc).
	 * @throws Ex\InvalidConfigurationElementException if config for specific class is technically invalid (i.e. not an array etc).
	 * @throws Ex\IncompleteConfigurationException if config for specific class is incomplete (misses some mandatory fields etc).
	 */
	protected static function getClassesMapping(): array
	{
		$classes = Config::get(RB::CONF_KEY_CONVERTER_CLASSES) ?? [];

		if (!\is_array($classes)) {
			throw new Ex\InvalidConfigurationException(
				\sprintf('"%s" must be an array (%s found)', RB::CONF_KEY_CONVERTER_CLASSES, \gettype($classes)));
		}

		if (!empty($classes)) {
			$mandatory_keys = [
				RB::KEY_HANDLER => [TYPE::STRING],
				RB::KEY_KEY => [TYPE::STRING, TYPE::NULL],
			];
			foreach ($classes as $class_name => $class_config) {
				if (!\is_array($class_config)) {
					throw new Ex\InvalidConfigurationElementException(
						sprintf("Config for '{$class_name}' class must be an array (%s found).", \gettype($class_config)));
				}
				foreach ($mandatory_keys as $key_name => $allowed_types) {
					if (!\array_key_exists($key_name, $class_config)) {
						throw new Ex\IncompleteConfigurationException(
							"Missing '{$key_name}' entry in '{$class_name}' class mapping config.");
					}

                    Validator::assertIsType(RB::KEY_KEY, $class_config[$key_name], $allowed_types);
				}
			}
		}

		return $classes;
	}

	/**
	 * Reads and validates "converter/primitives" config mapping
	 *
	 * @return array Primitives mapping config as specified in configuration or empty array if configuration found
	 *
	 * @throws Ex\InvalidConfigurationException if whole config mapping is technically invalid (i.e. not an array etc).
	 * @throws Ex\InvalidConfigurationElementException if config for specific class is technically invalid (i.e. not an array etc).
	 * @throws Ex\IncompleteConfigurationException if config for specific class is incomplete (misses some mandatory fields etc).
	 */
	protected static function getPrimitivesMapping(): array
	{
		$primitives = Config::get(RB::CONF_KEY_CONVERTER_PRIMITIVES) ?? [];

		if (!\is_array($primitives)) {
			throw new Ex\InvalidConfigurationException(
				\sprintf('"%s" mapping must be an array (%s found)', RB::CONF_KEY_CONVERTER_PRIMITIVES, \gettype($primitives)));
		}

		if (!empty($primitives)) {
			$mandatory_keys = [
				RB::KEY_KEY,
			];

			foreach ($primitives as $type => $config) {
				if (!\is_array($config)) {
					throw new Ex\InvalidConfigurationElementException(
						sprintf("Config for '{$type}' primitive must be an array (%s found).", \gettype($config)));
				}
				foreach ($mandatory_keys as $key_name) {
					if (!\array_key_exists($key_name, $config)) {
						throw new Ex\IncompleteConfigurationException(
							"Missing '{$key_name}' entry in '{$type}' primitive mapping config.");
					}
				}
			}
		}

		return $primitives;
	}
}
