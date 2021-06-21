<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Localization;

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
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class TranslationTest
 *
 * @package MarcinOrlowski\ResponseBuilder\Tests
 */
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
	    // We must NOT call langGet() wrapper as we want whole translation array
	    /** @var array $base_translations */
        $base_translations = \Lang::get('response-builder::builder');

        // get list of all other directories in library's lang folder.
	    /** @var array $entries */
	    $entries = glob(__DIR__ . '/../../../src/lang/*', GLOB_ONLYDIR);
	    $supported_languages =
		    array_filter(
			    array_filter(
				    array_map(static function($entry) {
					    return basename($entry);
				    }, $entries)
			    ),
			    static function($item) use ($default_lang) {
				    return $item !== $default_lang;
			    }
		    );

        $this->assertGreaterThan(0, \count($supported_languages));

        foreach ($supported_languages as $lang) {
            // get the translation array for given language
            \App::setLocale($lang);
	        // We must NOT call langGet() wrapper as we want whole translation array
	        /** @var array $translation */
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

