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

use MarcinOrlowski\ResponseBuilder\Tests\Providers\ResponseBuilderServiceProvider;

class MergeConfigTest extends TestCase
{

	/**
	 * Checks if ServiceProvider's configMerge properly merges multi-dimensional arrays
	 */
	public function testMergeConfig(): void
	{
		$sp = new ResponseBuilderServiceProvider(null);

		$key2_orig_val = $this->getRandomString('orig');
		$key2_new_val = $this->getRandomString('NEW');

		$key1_orig_val = $this->getRandomString('orig');
		$key1_new_val = $this->getRandomString('NEW');

		$original = [
			'key1' => [
				'key1_orig' => $key1_orig_val,
			],
			'key2' => $key2_orig_val,
		];

		$merging = [
			'key1' => [
				'key1_new' => $key1_new_val,
			],
			'key2' => $key2_new_val,
		];

		$merged = $this->callProtectedMethod($sp, 'mergeConfig', [$original,
		                                                          $merging]);

		$this->assertCount(2, $merged);
		$this->assertArrayHasKey('key1', $merged);
		$this->assertArrayHasKey('key2', $merged);

		$this->assertIsArray($merged['key1']);
		$this->assertCount(2, $merged['key1']);
		$this->assertArrayHasKey('key1_orig', $merged['key1']);
		$this->assertArrayHasKey('key1_new', $merged['key1']);
		$this->assertEquals($key1_orig_val, $merged['key1']['key1_orig']);
		$this->assertEquals($key1_new_val, $merged['key1']['key1_new']);

		$this->assertEquals($key2_new_val, $merged['key2']);
	}

}
