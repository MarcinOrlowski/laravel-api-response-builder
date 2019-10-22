<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TranslationTest extends TestCase
{
	/**
	 * Checks if translations are in par with base language
	 *
	 * @return void
	 */
	public function testTranslationFiles(): void
	{
		$base_lang = 'en';
		$supported_languages = ['pl'];

		\App::setLocale($base_lang);
		$base_translation = \Lang::get('response-builder::builder');

		foreach ($supported_languages as $lang) {
			// get the translation array for give language
			\App::setLocale($lang);
			$translation = \Lang::get('response-builder::builder');

			// ensure it has all the keys base translation do
			foreach ($base_translation as $key => $val) {
				$this->assertArrayHasKey($key, $translation);
				unset($translation[ $key ]);
			}
			// ensure we have no dangling translation entries left that
			// are no longer present in base translation.
			$this->assertEmpty($translation);
		}
	}
}

