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
    /**
     * @var array
     */
    protected $classes = [];

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

	    $this->debug_enabled = Config::get(ResponseBuilder::CONF_KEY_CONVERTER_DEBUG_KEY, false);
    }

    /**
     * Returns local copy of configuration mapping for the classes.
     *
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Checks if we have "classes" mapping configured for $data object class.
     * Returns @true if there's valid config for this class.
     * Throws \RuntimeException if there's no config "classes" mapping entry for this object configured.
     * Throws \InvalidArgumentException if No data conversion mapping configured for given class.
     *
     * @param object $data Object to check mapping for.
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
	 * Facade to data converting feature.
	 *
	 * NOTE: the `$data` payload passed to this method must be a complete payload to be returned
	 * in response `data` node. We need that to apply some "smart" behaviors while converting
	 * data to arrays to be returned.
	 *
	 * @param object|array|null $whole_payload
	 *
	 * @return array|null
	 */
	public function convertPayload($whole_payload = null): ?array
	{
		if ($whole_payload === null) {
			return null;
		}

		Validator::assertIsType('data', $whole_payload, [Validator::TYPE_ARRAY,
		                                                 Validator::TYPE_OBJECT]);

		$key = null;
		if (\is_object($data)) {
			$cfg = $this->getClassMappingConfigOrThrow($data);
			$worker = new $cfg[ ResponseBuilder::KEY_HANDLER ]();

		}

		if ($key !== null) {
			return [$key => $data];
		} else {
			return $data;
		}
    }

    /**
     * We need to prepare source data
     *
     * @param object|array|null $data
     *
     * @return array|null
     *
     * @throws \InvalidArgumentException
     */
    public function convert($data = null): ?array
    {
        if ($data === null) {
            return null;
        }

        Validator::assertIsType('data', $data, [Validator::TYPE_ARRAY,
                                                Validator::TYPE_OBJECT]);

	    if (\is_object($data)) {
		    $cfg = $this->getClassMappingConfigOrThrow($data);
            $worker = new $cfg[ ResponseBuilder::KEY_HANDLER ]();
		    $data = [$cfg[ ResponseBuilder::KEY_KEY ] => $worker->convert($data, $cfg)];
        } else {
		    $cls = \Illuminate\Contracts\Support\Arrayable::class;
		    if (\array_key_exists($cls, $this->classes)) {
			    $result = $this->classes[ $cls ];
			    $key = $this->classes[ $cls ][ ResponseBuilder::KEY_KEY ];
			    $debug_result = 'exact config match';
		    } else {
			    $key = ResponseBuilder::KEY_ITEMS;
			    $debug_result = 'arrayable hardcoded defaults';
		    }

	    	if ($this->hasNonNumericKeys($data)){
			    $data = $this->convertArray($data);
		    } else {
			    $data = [$key => $this->convertArray($data)];
		    }
        }

        return $data;
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
     * Reads and validates "classes" config mapping
     *
     * @return array Classes mapping as specified in configuration or empty array if configuration found
     *
     * @throws \RuntimeException if "classes" mapping is technically invalid (i.e. not array etc).
     */
    protected static function getClassesMapping(): array
    {
        $classes = Config::get(ResponseBuilder::CONF_KEY_CONVERTER);

        if ($classes !== null) {
            if (!\is_array($classes)) {
                throw new \RuntimeException(
                    \sprintf('CONFIG: "classes" mapping must be an array (%s given)', \gettype($classes)));
            }

            $mandatory_keys = [
                ResponseBuilder::KEY_HANDLER,
                ResponseBuilder::KEY_KEY,
            ];
            foreach ($classes as $class_name => $class_config) {
                foreach ($mandatory_keys as $key_name) {
                    if (!\array_key_exists($key_name, $class_config)) {
                        throw new \RuntimeException("CONFIG: Missing '{$key_name}' for '{$class_name}' class mapping");
                    }
                }
            }
        } else {
            $classes = [];
        }

        return $classes;
    }
}
