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

    /**
     * Converter constructor.
     *
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $this->classes = static::getClassesMapping() ?? [];
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

        // check for exact class name match...
        $cls = \get_class($data);
        if (\array_key_exists($cls, $this->classes)) {
            $result = $this->classes[ $cls ];
        } else {
            // no exact match, then lets try with `instanceof`
            foreach (\array_keys($this->getClasses()) as $class_name) {
                if ($data instanceof $class_name) {
                    $result = $this->classes[ $class_name ];
                    break;
                }
            }
        }

        if ($result === null) {
            throw new \InvalidArgumentException(sprintf('No data conversion mapping configured for "%s" class.', $cls));
        }

        return $result;
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
            $data = $worker->convert($data, $cfg);
        } else {
            $data = $this->convertArray($data);
        }

        return $data;
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
                    \sprintf('CONFIG: "classes" mapping must be an array (%s given)', gettype($classes)));
            }

            $mandatory_keys = [
                ResponseBuilder::KEY_HANDLER,
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
