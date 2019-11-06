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

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Builder;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class BuilderTest extends TestCase
{
    /**
     * Check plain success() invocation
     *
     * @return void
     */
    public function testSuccess(): void
    {
        $builder = Builder::success();

        $this->assertInstanceOf(Builder::class, $builder);
        $this->response = $builder->build();

        $expected_api_code = BaseApiCodes::OK();
        $j = $this->getResponseSuccessObject($expected_api_code);

        $this->assertNull($j->data);
        $this->assertEquals(\Lang::get(BaseApiCodes::getCodeMessageKey($expected_api_code)), $j->message);
    }
}
