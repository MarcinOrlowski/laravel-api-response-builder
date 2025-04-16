<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Converters;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2025 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Http\Resources\Json\JsonResource;
use MarcinOrlowski\ResponseBuilder\Contracts\ConverterContract;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\Validator;

/**
 * Generic object-to-array array converter.
 */
final class ToArrayConverter implements ConverterContract
{
    /**
     * Returns array representation of the object.
     *
     * @param object $obj    Object to be converted
     * @param array  $config Converter config array to be used for this object (based on exact class
     *                       name match or inheritance).
     *
     * @throws Ex\InvalidTypeException
     *
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceAfterLastUsed
     */
    public function convert(object $obj, array $config): array
    {
        Validator::assertIsObject('obj', $obj);

        /** @var JsonResource $obj */
        $request = request();
        return (array)$obj->toArray($request);
    }

} // end of class
