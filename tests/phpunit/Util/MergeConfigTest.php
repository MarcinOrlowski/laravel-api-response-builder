<?php
declare(strict_types=1);
/**
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace MarcinOrlowski\ResponseBuilder\Tests\Util;

/**
 * Laravel API Response Builder
 *
 * @author    Marcin Orlowski <mail (#) marcinOrlowski (.) com>
 * @copyright 2016-2024 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use MarcinOrlowski\PhpunitExtraAsserts\ExtraAsserts;
use MarcinOrlowski\PhpunitExtraAsserts\Generator;
use MarcinOrlowski\ResponseBuilder\Exceptions as Ex;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;
use MarcinOrlowski\ResponseBuilder\Util;

/**
 * Class MergeConfigTest
 */
class MergeConfigTest extends TestCase
{
    /**
     * Checks if config merger properly combines two arrays, preserving keys etc.
     */
    public function testConfigMerge(): void
    {
        $original = [
            'o1' => 'o1_val',
            'o2' => [
                'o2_k1' => 'o2_val',
                'o2_k2' => [
                    100 => [
                        'o2k2_100_k1' => 'o2k2_100_val',
                    ],
                    200 => 'o2k2_200_val',
                ],
                500     => 'os2_500_val',
            ],
        ];

        $merging = [
            'o1' => 'm_o2_val',
            'o2' => [
                'o2_k2' => [
                    100 => [
                        'm_o2k2_100_k1' => 'm_o2k2_100_val',
                    ],
                    200 => 'm_o2k2_200_val',
                ],
                500     => 'm_os2_500_val',
            ],
        ];

        $expected = [
            'o1' => 'm_o2_val',
            'o2' => [
                'o2_k1' => 'o2_val',
                'o2_k2' => [
                    100 => [
                        'o2k2_100_k1'   => 'o2k2_100_val',
                        'm_o2k2_100_k1' => 'm_o2k2_100_val',
                    ],
                    200 => 'm_o2k2_200_val',
                ],
                500     => 'm_os2_500_val',
            ],
        ];

        $result = Util::mergeConfig($original, $merging);
        ExtraAsserts::assertArrayEquals($result, $expected);
    }

    /**
     * Checks if config merger would fail when we try to feed it with two config sharing the key
     * but using data of different type in each configs.
     */
    public function testConfigMergeWithIncompatibleElements(): void
    {
        $key = Generator::getRandomString('key');
        $original = [$key => false];
        $merging = [$key => []];

        $this->expectException(Ex\IncompatibleTypeException::class);
        Util::mergeConfig($original, $merging);
    }
}
