<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

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

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\Converters\ToArrayConverter;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\Converters\FakeConverter;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModel;
use MarcinOrlowski\ResponseBuilder\Type;

class ObjectTest extends TestCase
{
	/**
	 * Checks if convert returns data without any wrapper 'key' when it's set to be empty
	 */
	public function testConvertWithEmptyKeyInConfig(): void
	{
        // custom converter with it's own response structure
        $fake = new FakeConverter();

        $data = collect([1, 2, 3]);

        $possibleEmptyKeyValues = ['', 0, '0', false, NULL];

        $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES);
        $cfg[ Collection::class ][ RB::KEY_HANDLER ] = FakeConverter::class;

        collect($possibleEmptyKeyValues)->each(function ($emptyKey) use($data, $fake, $cfg) {
            $cfg[ Collection::class ][ RB::KEY_KEY ] = $emptyKey;

            Config::set(RB::CONF_KEY_CONVERTER_CLASSES, $cfg);

            $result = (new Converter())->convert($data);

            $this->assertIsArray($result);
            $this->assertCount(1, $result);
            $this->assertArrayHasKey($fake->key, $result);
            $this->assertEquals($result[ $fake->key ], $fake->val);
        });
	}
}
