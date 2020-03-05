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

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection as SupportCollection;
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModelArrayable;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModelJsonResource;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModelJsonSerializable;

class DefaultConfigTest extends TestCase
{
    /**
     * Tests converter behavior on default config on object implementing Laravel's Arrayable interface.
     *
     * @return void
     */
    public function testArrayable(): void
    {
        // GIVEN object implementing Arrayable interface
        $obj_val = $this->getRandomString('val_1');
        $obj = new TestModelArrayable($obj_val);

        // HAVING converter with default settings
        $converter = new Converter();

        // WHEN we try to pass of object of that class
        $result = $converter->convert($obj);

        // THEN it should be converted automatically as per configuration
        $this->assertIsArray($result);
        $this->assertArrayHasKey('val', $result);
        $this->assertEquals($result['val'], $obj_val);
    }

    /**
     * Tests converter behavior on default config on object implementing JsonSerializable interface.
     *
     * @return void
     */
    public function testJsonSerializable(): void
    {
        // GIVEN JsonSerializable class object
        $obj_val = $this->getRandomString('obj_val');
        $obj = new TestModelJsonSerializable($obj_val);

        // HAVING converter with default settings
        $converter = new Converter();

        // WHEN we try to pass of object of that class
        $result = $converter->convert($obj);

        // THEN it should be converted automatically as per configuration
        $this->assertIsArray($result);
        $this->assertArrayHasKey('val', $result);
        $this->assertEquals($result['val'], $obj_val);
    }

    /**
     * Tests converter behavior on default config on object extending Laravel's JsonResource class.
     *
     * @return void
     */
    public function testJsonResource(): void
    {
        // GIVEN JSONResource class object
        $obj_val = $this->getRandomString('obj_val');
        $obj = new TestModelJsonResource($obj_val);

        // HAVING converter with default settings
        $converter = new Converter();

        // WHEN we try to pass of object of that class
        $result = $converter->convert($obj);

        // THEN it should be converted automatically as per configuration
        $this->assertIsArray($result);
        $this->assertArrayHasKey('val', $result);
        $this->assertEquals($result['val'], $obj_val);
    }

    /**
     * Tests converter behavior on default config on Laravel's Support\Collection.
     *
     * @return void
     */
    public function testSupportCollection(): void
    {
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = $this->getRandomString("item{$i}");
        }
        $this->doCollectionTests(collect($data));
    }

    /**
     * Tests converter behavior on default config on Laravel Eloquent's Collection.
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

        $this->doCollectionTests($collection);
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Helper method to perform some common tests of built-in support for Laravel's collections.
     *
     * @param object|array $data
     *
     * @return array
     */
    protected function doCollectionTests($data): array
    {
        // HAVING Converter with default settings
        // WHEN we try to pass of object of that class
        $result = (new Converter())->convert($data);

        // THEN it should be converted automatically as per default configuration
        $this->assertIsArray($result);

        foreach ($data as $key => $val) {
            $this->assertArrayHasKey($key, $result);
            $this->assertEquals($val, $result[ $key ]);
        }

        return $result;
    }
}
