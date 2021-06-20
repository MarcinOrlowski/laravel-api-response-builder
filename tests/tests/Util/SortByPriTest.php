<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\Util;

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

use MarcinOrlowski\ResponseBuilder\Util;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;

/**
 * Class SortByPriTest
 *
 * @package MarcinOrlowski\ResponseBuilder\Tests
 */
class SortByPriTest extends TestCase
{
    /**
     * Checks if config merger properly combines two arrays, preserving keys etc.
     */
    public function testSortByPri(): void
    {
        $key1 = $this->getRandomString('key_1');
        $key2 = $this->getRandomString('key_2');
        $key3 = $this->getRandomString('key_3');
        $key4 = $this->getRandomString('key_4');

        $data = [
            $key1 => ['pri' => -1,],
            $key2 => ['pri' => +5,],
            $key3 => ['pri' => 0,],
            $key4 => ['pri' => +2,],
        ];

        Util::sortArrayByPri($data);

        $this->assertCount(4, $data);
        $keys = array_keys($data);
        $this->assertEquals($key2, $keys[0]);
        $this->assertEquals($key4, $keys[1]);
        $this->assertEquals($key3, $keys[2]);
        $this->assertEquals($key1, $keys[3]);
    }
}
