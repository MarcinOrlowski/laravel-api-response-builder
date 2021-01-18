<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

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

use Illuminate\Support\Facades\Config;

class TranslationTest extends TestCase
{
    /**
     * Checks if translations are in par with base language
     *
     * @return void
     */
    public function testTranslationFiles(): void
    {
        // default library language
        $default_lang = 'en';

        // Load translation array for default language and then compare all the
        // other translations with it.
        \App::setLocale($default_lang);
        $base_translations = \Lang::get('response-builder::builder');

        // get list of all other directories in library's lang folder.
        $supported_languages =
            array_filter(
                array_filter(array_map(function($entry) {
                    return basename($entry);
                }, glob(__DIR__ . '/../../../src/lang/*', GLOB_ONLYDIR))),
                function($item) use ($default_lang) {
                    return $item != $default_lang;
                }
            );

        $this->assertGreaterThan(0, \count($supported_languages));

        foreach ($supported_languages as $lang) {
            // get the translation array for given language
            \App::setLocale($lang);
            $translation = \Lang::get('response-builder::builder');

            // ensure it has all the keys base translation do
            foreach ($base_translations as $key => $val) {
                $msg = "Missing localization entry '{$key}' in '{$lang}' language file.";
                $this->assertArrayHasKey($key, $translation, $msg);
                unset($translation[ $key ]);
            }
            // ensure we have no dangling translation entries left that
            // are no longer present in base translation.
            $sep = "\n   ";
            $msg = "Unwanted entries in '{$lang}' language file:{$sep}" . implode($sep, array_keys($translation));
            $this->assertEmpty($translation, $msg);
        }
    }
}

