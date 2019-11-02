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

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection as SupportCollection;
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModelJsonResource;

class DefaultConfigTest extends TestCase
{
    /**
     * Tests built-in support for JsonResource class on default
     *
     * @return void
     */
    public function testJsonResource(): void
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

    /**
     * Tests built-in support for Support\Collection class on defaults
     *
     * @return void
     */
    public function testSupportCollection(): void
    {
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = $this->getRandomString("item{$i}");
        }
        $this->doCollectionTest(collect($data));
    }

    /**
     * Tests built-in support for Eloquent's Collection class on defaults
     *
     * @return void
     */
    public function testEloquentCollection(): void
    {
        // GIVEN Eloquent collection with content
        $collection = new EloquentCollection();
        $collection->add($this->getRandomString('item1'));
        $collection->add($this->getRandomString('item2'));
        $collection->add($this->getRandomString('item3'));

        $this->doCollectionTest($collection);
    }

    /**
     * Do the testing of collection type of object.
     *
     * @param object $collection
     *
     * @return void
     */
    protected function doCollectionTest($collection): void
    {
        // GIVEN Eloquent collection with content

        // HAVING Converter with default settings
        $converter = new Converter();
        $cfg = $converter->getClasses();

        // WHEN we try to pass of object of that class
        $result = $converter->convert($collection);

        // THEN it should be converted automatically as per default configuration
        $this->assertIsArray($result);

        $cfg_key = $cfg[ get_class($collection) ][ ResponseBuilder::KEY_KEY ];
        $this->assertArrayHasKey($cfg_key, $result);
        $this->assertIsArray($result[ $cfg_key ]);
        $this->assertCount(count($collection), $result[ $cfg_key ]);
        foreach ($collection as $key => $val) {
            $this->assertArrayHasKey($key, $result[ $cfg_key ]);
            $this->assertEquals($val, $result[ $cfg_key ][ $key ]);
        }

    }
}
