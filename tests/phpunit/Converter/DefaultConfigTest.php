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
 * @copyright 2016-2024 Marcin Orlowski
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
        /** @var array $cfg */
        $key = $cfg[ \Illuminate\Contracts\Support\Arrayable::class ][ RB::KEY_KEY ];

        $this->assertIsArray($result);
        /** @var array $result */
        $this->assertArrayHasKey($key, $result);
        /** @var array $result */
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
            /** @var array $cfg */

            $this->assertNotEmpty($cfg);
            $this->assertIsArray($cfg);

            $key = $cfg[ \JsonSerializable::class ][ RB::KEY_KEY ];

            $this->assertIsArray($result);
            /** @var array $result */
            $this->assertArrayHasKey($key, $result);
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
        /** @var array $cfg */
        $this->assertNotEmpty($cfg);
        $this->assertIsArray($cfg);
        $key = $cfg[ \Illuminate\Http\Resources\Json\JsonResource::class ][ RB::KEY_KEY ];

        $this->assertIsArray($result);
        /** @var array $result */
        $this->assertArrayHasKey($key, $result);
        $result = $result[ $key ];
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
        /** @phpstan-ignore-next-line */
        $collection->add(Generator::getRandomString('item1'));
        /** @phpstan-ignore-next-line */
        $collection->add(Generator::getRandomString('item2'));
        /** @phpstan-ignore-next-line */
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
        /** @noinspection PhpParamsInspection */
        $this->doPaginatorSupportTests(
            new \Illuminate\Pagination\LengthAwarePaginator(collect($data), $total, (int)($total / 2)));
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

        /** @noinspection PhpParamsInspection */
        $this->doPaginatorSupportTests(
            new \Illuminate\Pagination\Paginator(collect($data), (int)(\count($data) / 2)));
    }

    /**
     * Helper that performs common tests for Paginator support.
     */
    protected function doPaginatorSupportTests(AbstractPaginator $paginator): void
    {
        $result = (new Converter())->convert($paginator);
        ExtraAsserts::assertIsArray($result);
        /** @var array $result */
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
     */
    protected function doCollectionTests(object $collection): array
    {
        // HAVING Converter with default settings
        // WHEN we try to pass of object of that class
        /** @var array $result */
        $result = (new Converter())->convert($collection);

        // THEN it should be converted automatically as per default configuration
        /** @var array $cfg */
        $cfg = Config::get(RB::CONF_KEY_CONVERTER_CLASSES);
        $key = $cfg[ \get_class($collection) ][ RB::KEY_KEY ];

        $this->assertIsArray($result);
        $this->assertArrayHasKey($key, $result);
        $result = $result[ $key ];

        $this->assertIsIterable($collection);
        /**
         * @var iterable $collection
         * @var  string  $key
         */
        foreach ($collection as $key => $val) {
            $this->assertArrayHasKey($key, $result);
            $this->assertEquals($val, $result[ $key ]);
        }

        return $result;
    }

} // end of class
