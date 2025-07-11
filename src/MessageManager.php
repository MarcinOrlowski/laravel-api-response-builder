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

use Illuminate\Support\Facades\Lang;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;

class MessageManager
{
    /**
     * If $msg_or_api_code is integer value, returns human readable message associated with that code
     * (with fallback to built-in default string if no api code mapping is set. If $msg_or_api_code
     * is a string,  returns it unaltered.
     *
     * @param boolean    $success      TRUE if response reports successful operation, FALSE otherwise.
     * @param integer    $api_code     Your API code to be returned with the response object.
     * @param array<string, mixed>|null $placeholders Placeholders passed to Lang::get() for message placeholders
     *                                 substitution or NULL if none.
     *
     * @throws Ex\IncompatibleTypeException
     * @throws Ex\MissingConfigurationKeyException
     * @throws Ex\InvalidTypeException
     * @throws Ex\NotIntegerException
     */
    public static function get(bool   $success,
                               int    $api_code,
                               ?array $placeholders = null): string
    {
        // We got integer value here not a message string, so we need to check if we have the mapping for
        // this string already configured.
        $key = BaseApiCodes::getCodeMessageKey($api_code);
        if ($key === null) {
            // nope, let's get the default one instead, based of
            $fallback_code = $success ? BaseApiCodes::OK() : BaseApiCodes::NO_ERROR_MESSAGE();
            // default messages are expected to be always available
            /** @var string $key */
            $key = BaseApiCodes::getCodeMessageKey($fallback_code);
        }

        $placeholders = $placeholders ?? [];
        if (!\array_key_exists('api_code', $placeholders)) {
            $placeholders['api_code'] = $api_code;
        }

        // As Lang::get() is documented to also returning whole language arrays,
        // so static analysers will alarm if that case is not taken care of.
        $msg = Lang::get($key, $placeholders);
        if (\is_array($msg)) {
            $msg = \implode('', $msg);
        }

        return $msg;
    }
}
