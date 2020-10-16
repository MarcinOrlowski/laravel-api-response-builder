<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2020 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;


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
     *
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $this->classes = static::getClassesMapping() ?? [];
        $this->primitives = static::getPrimitivesMapping() ?? [];

	    $this->debug_enabled = Config::get(ResponseBuilder::CONF_KEY_CONVERTER_DEBUG_KEY, false);
    }

    /**
     * Returns local copy of configuration mapping for data classes.
     *
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

	/**
	 * Returns local copy of configuration mapping for primitives.
	 *
	 * @return array
	 */
	public function getPrimitives(): array
    {
    	return $this->primitives;
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
	 * @throws \InvalidArgumentException
	 */
    protected function getPrimitiveMappingConfigOrThrow($data): array
    {
	    $result = null;

	    $type = \gettype($data);
	    $result = $this->primitives[ $type ] ?? null;
	    if ($result === null) {
		    throw new \InvalidArgumentException(sprintf('No data conversion mapping configured for "%s" primitive.', $type));
	    }

	    if ($this->debug_enabled) {
		    Log::debug(__CLASS__ . ": Converting primitive type of '{$type}' to data node '{$result[ResponseBuilder::KEY_KEY]}'.");
	    }

	    return $result;
    }

    /**
     * Returns "converter/map" mapping configured for given $data object class or throws exception if not found.
     * Throws \RuntimeException if there's no config "classes" mapping entry for this object configured.
     * Throws \InvalidArgumentException if No data conversion mapping configured for given class.
     *
     * @param object $data Object to get config for.
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getClassMappingConfigOrThrow(object $data): array
    {
        $result = null;
        $debug_result = '';

        // check for exact class name match...
        $cls = \get_class($data);
        if (\is_string($cls)) {
	        if (\array_key_exists($cls, $this->classes)) {
		        $result = $this->classes[ $cls ];
		        $debug_result = 'exact config match';
	        } else {
		        // no exact match, then lets try with `instanceof`
		        foreach (\array_keys($this->getClasses()) as $class_name) {
			        if ($data instanceof $class_name) {
				        $result = $this->classes[ $class_name ];
				        $debug_result = "subclass of {$class_name}";
				        break;
			        }
		        }
	        }
        }

        if ($result === null) {
            throw new \InvalidArgumentException(sprintf('No data conversion mapping configured for "%s" class.', $cls));
        }

        if ($this->debug_enabled) {
			Log::debug(__CLASS__ . ": Converting {$cls} using {$result[ResponseBuilder::KEY_HANDLER]} because: {$debug_result}.");
        }

	    return $result;
    }

	/**
	 * Checks if we have "classes" mapping configured given class name.
	 * Returns @true if there's valid config for this class.
	 * Throws \RuntimeException if there's no config "classes" mapping entry for this object configured.
	 * Throws \InvalidArgumentException if No data conversion mapping configured for given class.
	 *
	 * @param string $cls Name of the class to check mapping for.
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function getClassMappingConfigOrThrowByName(string $cls): array
	{
		$result = null;
		$debug_result = '';

		// check for exact class name match...
		if (\array_key_exists($cls, $this->classes)) {
			$result = $this->classes[ $cls ];
			$debug_result = 'exact config match';
		}

		if ($result === null) {
			throw new \InvalidArgumentException(sprintf('No data conversion mapping configured for "%s" class.', $cls));
		}

		if ($this->debug_enabled) {
			Log::debug(__CLASS__ . ": Converting {$cls} using {$result[ResponseBuilder::KEY_HANDLER]} because: {$debug_result}.");
		}

		return $result;
	}

    /**
     * Main entry for data conversion
     *
     * @param object|array|null $data
     *
     * @return mixed|null
     *
     * @throws \InvalidArgumentException
     */
    public function convert($data = null): ?array
    {
        if ($data === null) {
            return null;
        }

        $result = null;

	    Validator::assertIsType('data', $data, [
		    Validator::TYPE_ARRAY,
		    Validator::TYPE_BOOL,
		    Validator::TYPE_DOUBLE,
		    Validator::TYPE_INTEGER,
		    Validator::TYPE_STRING,
		    Validator::TYPE_OBJECT,
	    ]);

	    if ($result === null && \is_object($data)) {
		    $cfg = $this->getClassMappingConfigOrThrow($data);
		    $worker = new $cfg[ ResponseBuilder::KEY_HANDLER ]();
		    $result = [$cfg[ ResponseBuilder::KEY_KEY ] => $worker->convert($data, $cfg)];
	    }

	    if ($result === null && \is_array($data)) {
	        $cfg = $this->getPrimitiveMappingConfigOrThrow($data);

	        if ($this->hasNonNumericKeys($data)){
		        $result = $this->convertArray($data);
	        } else {
		        $result = [$cfg[ ResponseBuilder::KEY_KEY ] => $this->convertArray($data)];
	        }
        }

	    if ( \is_bool($data) || \is_float($data) || \is_int($data) || \is_string($data)) {
		    $result = [$this->getPrimitiveMappingConfigOrThrow($data)[ ResponseBuilder::KEY_KEY ] => $data];
	    }

	    return $result;
    }

    protected function hasNonNumericKeys(array $data): bool
    {
	    foreach ($data as $key => $val) {
	    	if (!\is_int($key)) {
	    		return true;
		    }
    	}

	    return false;
    }

    /**
     * Recursively walks $data array and converts all known objects if found. Note
     * $data array is passed by reference so source $data array may be modified.
     *
     * @param array $data array to recursively convert known elements of
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    protected function convertArray(array $data): array
    {
        // This is to ensure that we either have array with user provided keys i.e. ['foo'=>'bar'], which will then
        // be turned into JSON object or array without user specified keys (['bar']) which we would return as JSON
        // array. But you can't mix these two as the final JSON would not produce predictable results.
        $string_keys_cnt = 0;
        $int_keys_cnt = 0;
        foreach ($data as $key => $val) {
            if (\is_int($key)) {
                $int_keys_cnt++;
            } else {
                $string_keys_cnt++;
            }

            if (($string_keys_cnt > 0) && ($int_keys_cnt > 0)) {
                throw new \RuntimeException(
                    'Invalid data array. Either set own keys for all the items or do not specify any keys at all. ' .
                    'Arrays with mixed keys are not supported by design.');
            }
        }

        foreach ($data as $key => $val) {
            if (\is_array($val)) {
                $data[ $key ] = $this->convertArray($val);
            } elseif (\is_object($val)) {
                $cfg = $this->getClassMappingConfigOrThrow($val);
                $worker = new $cfg[ ResponseBuilder::KEY_HANDLER ]();
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
     * @throws \RuntimeException if config mapping is technically invalid (i.e. not array etc).
     */
    protected static function getClassesMapping(): array
    {
        $classes = Config::get(ResponseBuilder::CONF_KEY_CONVERTER_MAP) ?? [];

	    if (!\is_array($classes)) {
		    throw new \RuntimeException(
			    \sprintf('CONFIG: "%s" mapping must be an array (%s given)', ResponseBuilder::CONF_KEY_CONVERTER_MAP, \gettype($classes)));
	    }

	    if (!empty($classes)) {
		    $mandatory_keys = [
			    ResponseBuilder::KEY_HANDLER,
			    ResponseBuilder::KEY_KEY,
		    ];
		    foreach ($classes as $class_name => $class_config) {
			    if (!\is_array($class_config)) {
				    throw new \InvalidArgumentException(sprintf("CONFIG: Config for '{$class_name}' class must be an array (%s given).", \gettype($class_config)));
			    }
			    foreach ($mandatory_keys as $key_name) {
				    if (!\array_key_exists($key_name, $class_config)) {
					    throw new \RuntimeException("CONFIG: Missing '{$key_name}' for '{$class_name}' class mapping");
				    }
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
	 * @throws \RuntimeException if config mapping is technically invalid (i.e. not array etc).
	 */
	protected static function getPrimitivesMapping(): array
	{
		$primitives = Config::get(ResponseBuilder::CONF_KEY_CONVERTER_PRIMITIVES) ?? [];

		if (!\is_array($primitives)) {
			throw new \RuntimeException(
				\sprintf('CONFIG: "%s" mapping must be an array (%s given)', ResponseBuilder::CONF_KEY_CONVERTER_PRIMITIVES, \gettype($primitives)));
		}

		if (!empty($primitives)) {
			$mandatory_keys = [
				ResponseBuilder::KEY_KEY,
			];

			foreach ($primitives as $type => $config) {
				if (!\is_array($config)) {
					throw new \InvalidArgumentException(sprintf("CONFIG: Config for '{$type}' primitive must be an array (%s given).", \gettype($config)));
				}
				foreach ($mandatory_keys as $key_name) {
					if (!\array_key_exists($key_name, $config)) {
						throw new \RuntimeException("CONFIG: Missing '{$key_name}' for '{$type}' primitive mapping");
					}
				}
			}
		}

		return $primitives;
	}
}
