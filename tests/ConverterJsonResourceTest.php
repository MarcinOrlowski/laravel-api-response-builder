<?php

namespace MarcinOrlowski\ResponseBuilder\Tests;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail (#) marcinorlowski (.) com>
 * @copyright 2016-2019 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ConverterJsonResourceTest extends TestCase
{
	/**
	 * Tests built-in support for JsonResource class on default
	 */
	public function testConverter_JsonResource(): void
	{
		// GIVEN JSONResource class object
		$obj_val = $this->getRandomString('obj_val');
		$obj = new TestModelJsonResource($obj_val);

		$converter = new Converter();
		$cfg = $converter->getClasses();

		// WHEN we try to pass of object of that class
		$result = $converter->convert($obj);

		// THEN it should be converted automatically as per configuration
		$this->assertIsArray($result);

		$key = $cfg[ JsonResource::class ][ ResponseBuilder::KEY_KEY ];
		$this->assertArrayHasKey($key, $result);
		$this->assertIsArray($result[ $key ]);
		$this->assertArrayHasKey('val', $result[ $key ]);
		$this->assertEquals($result[ $key ]['val'], $obj_val);
	}

}
