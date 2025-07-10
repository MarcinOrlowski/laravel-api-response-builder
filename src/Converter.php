<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2025 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Contracts\ConverterContract;

/**
 * Data converter
 */
class Converter
{
    /** @var array<string, mixed> */
    protected $classes = [];

    /** @var array<string, mixed> */
    protected $primitives = [];

    /** @var bool */
    protected $debug_enabled = false;

    /**
     * Converter constructor.
     *
     * @throws Ex\InvalidConfigurationException if whole config mapping is technically invalid (i.e. not an array etc).
     * @throws Ex\InvalidConfigurationElementException if config for specific class is technically invalid (i.e. not an array etc).
     * @throws Ex\IncompleteConfigurationException if config for specific class is incomplete (misses some mandatory fields etc).
     * @throws Ex\InvalidTypeException
     */
    public function __construct()
    {
        /** @var array<string, mixed> $classes */
        $classes = static::getClassesMapping();
        /** @var array<string, mixed> $primitives */
        $primitives = static::getPrimitivesMapping();

        $this->classes = $classes;
        $this->primitives = $primitives;

        $this->debug_enabled = (bool)Config::get(RB::CONF_KEY_DEBUG_CONVERTER_DEBUG_ENABLED, false);
    }

    /**
     * Returns "converter/primitives" entry for given primitive object or throws exception if no config found.
     * Throws \RuntimeException if there's no config "classes" mapping entry for this object configured.
     * Throws \InvalidArgumentException if No data conversion mapping configured for given class.
     *
     * @param boolean|string|double|array<string, mixed>|int $data Primitive to get config for.
     *
     * @return array<string, mixed>
     * @throws Ex\ConfigurationNotFoundException
     */
    protected function getPrimitiveMappingConfigOrThrow($data): array
    {
        $type = \gettype($data);
        $result = $this->primitives[ $type ] ?? null;

        if ($result === null) {
            throw new Ex\ConfigurationNotFoundException(
                sprintf('No data conversion mapping configured for "%s" primitive.', $type));
        }

        /** @var array<string, mixed> $result */
        if ($this->debug_enabled) {
            $keyValue = $result[RB::KEY_KEY] ?? '';
            /** @var string|int|float|bool|null $keyValue */
            $key = \is_string($keyValue) ? $keyValue : \strval($keyValue);
            Log::debug(__CLASS__ . ": Converting primitive type of '{$type}' to data node with key '{$key}'.");
        }

        return $result;
    }

    /**
     * Returns "converter/map" mapping configured for given $data object class or throws exception if not found.
     *
     * @param object $data Object to get config for.
     * @return array<string, mixed>
     *
     * @throws Ex\ConfigurationNotFoundException
     */
    protected function getClassMappingConfigOrThrow(object $data): array
    {
        $result = null;
        $debug_result = '';

        // check for exact class name match...
        $cls = \get_class($data);
        if (\array_key_exists($cls, $this->classes)) {
            $result = $this->classes[ $cls ];
            $debug_result = 'exact config match';
        } else {
            // no exact match, then lets try with `instanceof`
            foreach (\array_keys($this->classes) as $class_name) {
                /** @var string $class_name */
                if ($data instanceof $class_name) {
                    $result = $this->classes[ $class_name ];
                    $debug_result = "subclass of {$class_name}";
                    break;
                }
            }
        }

        if ($result === null) {
            throw new Ex\ConfigurationNotFoundException(
                sprintf('No data conversion mapping configured for "%s" class.', $cls));
        }

        /** @var array<string, mixed> $result */
        if ($this->debug_enabled) {
            $handlerValue = $result[RB::KEY_HANDLER] ?? '';
            /** @var string|int|float|bool|null $handlerValue */
            $handler = \is_string($handlerValue) ? $handlerValue : \strval($handlerValue);
            Log::debug(__CLASS__ . ": Converting {$cls} using {$handler} because: {$debug_result}.");
        }

        return $result;
    }

    /**
     * Main entry for data conversion
     *
     * @param mixed|null $data
     * @return array<string, mixed>|null
     *
     * @throws Ex\ConfigurationNotFoundException
     * @throws Ex\ArrayWithMixedKeysException
     * @throws Ex\InvalidTypeException
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

        if (\is_object($data)) {
            $cfg = $this->getClassMappingConfigOrThrow($data);
            $worker = new $cfg[ RB::KEY_HANDLER ]();
            /** @var ConverterContract $worker */
            $result = $worker->convert($data, $cfg);
            $result = $cfg[ RB::KEY_KEY ] === null ? $result : [$cfg[ RB::KEY_KEY ] => $result];
        } elseif (\is_array($data)) {
            /** @var array<string, mixed> $arrayData */
            $arrayData = $data;
            $cfg = $this->getPrimitiveMappingConfigOrThrow($arrayData);

            $result = $this->convertArray($arrayData);
            if (!Util::isArrayWithNonNumericKeys($arrayData)) {
                $result = [$cfg[ RB::KEY_KEY ] => $result];
            }
        } elseif (\is_bool($data) || \is_float($data) || \is_int($data) || \is_string($data)) {
            $result = [$this->getPrimitiveMappingConfigOrThrow($data)[ RB::KEY_KEY ] => $data];
        }

        /** @var array<string, mixed>|null $result */
        return $result;
    }

    /**
     * Recursively walks $data array and converts all known objects if found. Note
     * $data array is passed by reference so source $data array may be modified.
     *
     * @param array<string, mixed> $data array to recursively convert known elements of
     * @return array<string, mixed>
     *
     * @throws Ex\ConfigurationNotFoundException
     * @throws Ex\ArrayWithMixedKeysException
     */
    protected function convertArray(array $data): array
    {
        Validator::assertArrayHasNoMixedKeys($data);

        foreach ($data as $key => $val) {
            if (\is_array($val)) {
                /** @var array<string, mixed> $arrayVal */
                $arrayVal = $val;
                $data[ $key ] = $this->convertArray($arrayVal);
            } elseif (\is_object($val)) {
                $cfg = $this->getClassMappingConfigOrThrow($val);
                $worker = new $cfg[ RB::KEY_HANDLER ]();
                /** @var ConverterContract $worker */
                $converted_data = $worker->convert($val, $cfg);
                $data[ $key ] = $converted_data;
            }
        }

        return $data;
    }

    /**
     * Reads and validates "converter/map" config mapping. Returns Classes mapping as specified in
     * configuration or empty array if configuration found.
     *
     * @return array<string, mixed>
     *
     * @throws Ex\InvalidConfigurationException if whole config mapping is technically invalid (i.e. not an array etc).
     * @throws Ex\InvalidConfigurationElementException if config for specific class is technically invalid (i.e. not an array etc).
     * @throws Ex\IncompleteConfigurationException if config for specific class is incomplete (misses some mandatory fields etc).
     * @throws Ex\InvalidTypeException
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
                RB::KEY_HANDLER => [Type::STRING],
                RB::KEY_KEY     => [Type::STRING, Type::NULL],
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

                    Validator::assertIsType(RB::KEY_KEY, $class_config[ $key_name ], $allowed_types);
                }
            }
        }

        /** @var array<string, mixed> $classes */
        return $classes;
    }

    /**
     * Reads and validates "converter/primitives" config mapping. Returns primitives mapping config
     * as specified in configuration or empty array if configuration found.
     *
     * @return array<string, mixed>
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

        /** @var array<string, mixed> $primitives */
        return $primitives;
    }

} // end of class
