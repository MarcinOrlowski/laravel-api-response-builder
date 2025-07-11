<?php
/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Converter;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2025 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Config;
use MarcinOrlowski\PhpunitExtraAsserts\ExtraAsserts;
use MarcinOrlowski\PhpunitExtraAsserts\Generator;
use MarcinOrlowski\ResponseBuilder\Converter;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModelArrayable;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModelJsonResource;
use MarcinOrlowski\ResponseBuilder\Tests\Models\TestModelJsonSerializable;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;
use PHPUnit\Framework\Assert;

/**
 * Class DefaultConfigTest
 */
class DefaultConfigTest extends TestCase
{
    /**
     * Tests converter behavior on default config on object implementing Laravel's Arrayable interface.
     */
    public function testArrayable(): void
    {
        // GIVEN object implementing Arrayable interface
        $obj_val = Generator::getRandomString('val_1');
        $obj = new TestModelArrayable($obj_val);

        // HAVING converter with default settings
        $converter = new Converter();

        // WHEN we try to pass of object of that class
        $result = $converter->convert($obj);

        // THEN it should be converted automatically as per configuration
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES);
        $this->assertNotEmpty($cfg);
        $this->assertIsArray($cfg);
        /** @var array<string, mixed> $cfg */
        /** @var array<string, mixed> $arrayable_config */
        $arrayable_config = $cfg[ \Illuminate\Contracts\Support\Arrayable::class ];
        $key = $arrayable_config[ RB::KEY_KEY ];

        $this->assertIsArray($result);
        /** @var array<string, mixed> $result */
        $this->assertArrayHasKey($key, $result);
        /** @var array<string, mixed> $result */
        $result = $result[ $key ];
        $this->assertArrayHasKey(TestModelArrayable::FIELD_NAME, $result);
        $this->assertEquals($result[ TestModelArrayable::FIELD_NAME ], $obj_val);
    }

    /**
     * Tests converter behavior on default config on object implementing JsonSerializable interface.
     */
    public function testJsonSerializable(): void
    {
        $values = [
            Generator::getRandomString('obj_val'),
            [Generator::getRandomString('obj_a'),
             Generator::getRandomString('obj_b')],
            mt_rand(),
        ];

        foreach ($values as $obj_val) {
            // GIVEN JsonSerializable class object
            $obj = new TestModelJsonSerializable($obj_val);

            // HAVING converter with default settings
            $converter = new Converter();

            // WHEN we try to pass of object of that class
            $result = $converter->convert($obj);

            // THEN it should be converted automatically as per configuration
            $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES);
            /** @var array<string, mixed> $cfg */

            $this->assertNotEmpty($cfg);
            $this->assertIsArray($cfg);

            /** @var array<string, mixed> $json_config */
            $json_config = $cfg[ \JsonSerializable::class ];
            $key = $json_config[ RB::KEY_KEY ];

            $this->assertIsArray($result);
            /** @var array<string, mixed> $result */
            $this->assertArrayHasKey($key, $result);
            /** @var array<string, mixed> $result */
            $result = $result[ $key ];
            $this->assertArrayHasKey($key, $result);
            $this->assertEquals($obj_val, $result[ $key ]);
        }
    }

    /**
     * Tests converter behavior on default config on object extending Laravel's JsonResource class.
     */
    public function testJsonResource(): void
    {
        // GIVEN JSONResource class object
        $obj_val = Generator::getRandomString('obj_val');
        $obj = new TestModelJsonResource($obj_val);

        // HAVING converter with default settings
        $converter = new Converter();

        // WHEN we try to pass of object of that class
        $result = $converter->convert($obj);

        // THEN it should be converted automatically as per configuration
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES);
        /** @var array<string, mixed> $cfg */
        $this->assertNotEmpty($cfg);
        $this->assertIsArray($cfg);
        /** @var array<string, mixed> $json_resource_config */
        $json_resource_config = $cfg[ \Illuminate\Http\Resources\Json\JsonResource::class ];
        $key = $json_resource_config[ RB::KEY_KEY ];

        $this->assertIsArray($result);
        /** @var array<string, mixed> $result */
        $this->assertArrayHasKey($key, $result);
        $result = $result[ $key ];
        /** @var array<string, mixed> $result */
        $this->assertArrayHasKey(TestModelJsonResource::FIELD_NAME, $result);
        $this->assertEquals($result[ TestModelJsonResource::FIELD_NAME ], $obj_val);
    }

    /**
     * Tests converter behavior on default config on Laravel's Support\Collection.
     */
    public function testSupportCollection(): void
    {
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = Generator::getRandomString("item{$i}");
        }
        $this->doCollectionTests(collect($data));
    }

    /**
     * Tests converter behavior on default config on Laravel Eloquent's Collection.
     */
    public function testEloquentCollection(): void
    {
        // GIVEN Eloquent collection with content
        $collection = new EloquentCollection();
        $collection->add(Generator::getRandomString('item1'));
        $collection->add(Generator::getRandomString('item2'));
        $collection->add(Generator::getRandomString('item3'));

        $this->doCollectionTests($collection);
    }

    // ---------------------------------------------------------------------------------------------

    /**
     * Checks if default config for LengthAwarePaginator class produces expected output.
     */
    public function testLengthAwarePaginator(): void
    {
        $data = [];
        for ($i = 0; $i < \random_int(10, 20); $i++) {
            $data[] = Generator::getRandomString("item{$i}");
        }
        $total = \count($data);
        /** @var \Illuminate\Pagination\LengthAwarePaginator<int, string> $paginator */
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(collect($data), $total, (int)($total / 2));
        $this->doPaginatorSupportTests($paginator);
    }

    /**
     * Checks if default config for Paginator class produces expected output.
     */
    public function testPaginator(): void
    {
        $data = [];
        for ($i = 0; $i < \random_int(10, 20); $i++) {
            $data[] = Generator::getRandomString("item{$i}");
        }

        /** @var \Illuminate\Pagination\Paginator<int, string> $paginator */
        $paginator = new \Illuminate\Pagination\Paginator(collect($data), (int)(\count($data) / 2));
        $this->doPaginatorSupportTests($paginator);
    }

    /**
     * Helper that performs common tests for Paginator support.
     *
     * @param AbstractPaginator<int, string> $paginator
     */
    protected function doPaginatorSupportTests(AbstractPaginator $paginator): void
    {
        $result = (new Converter())->convert($paginator);
        ExtraAsserts::assertIsArray($result);
        /** @var array<string, mixed> $result */
        ExtraAsserts::assertArrayHasKeys([
            'current_page',
            'data',
            'first_page_url',
            'from',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
        ], $result);
    }

    // ---------------------------------------------------------------------------------------------

    /**
     * Helper method to perform some common tests of built-in support for Laravel's collections.
     *
     * @return array<string, mixed>
     */
    protected function doCollectionTests(object $collection): array
    {
        // HAVING Converter with default settings
        // WHEN we try to pass of object of that class
        /** @var array<string, mixed> $result */
        $result = (new Converter())->convert($collection);

        // THEN it should be converted automatically as per default configuration
        /** @var array<string, mixed> $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES);
        /** @var array<string, mixed> $collection_config */
        $collection_config = $cfg[ \get_class($collection) ];
        $key = $collection_config[ RB::KEY_KEY ];

        $this->assertIsArray($result);
        $this->assertArrayHasKey($key, $result);
        $result = $result[ $key ];

        $this->assertIsIterable($collection);
        /**
         * @var iterable<string, mixed> $collection
         * @var  string  $foreach_key
         */
        foreach ($collection as $foreach_key => $val) {
            /** @var array<string, mixed> $result */
            $this->assertArrayHasKey($foreach_key, $result);
            $this->assertEquals($val, $result[ $foreach_key ]);
        }

        /** @var array<string, mixed> $result */
        return $result;
    }

} // end of class
