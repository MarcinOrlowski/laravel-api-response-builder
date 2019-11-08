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

use MarcinOrlowski\ResponseBuilder\Validator;

class ValidatorTest extends TestCase
{
    /**
     * Tests if assertInt() pass if given valid data.
     *
     * @return void
     */
    public function testAssertIntCorrectType(): void
    {
        Validator::assertInt(__FUNCTION__, 666);
        // This assert won't be called if exception is thrown
        $this->assertTrue(true);
    }

    /**
     * Tests if assertInt() throws exception when feed with invalid type argument.
     *
     * @return void
     */
    public function testAssertIntWrongType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Validator::assertInt(__FUNCTION__, 'chicken');
    }


    /**
     * Tests if assertString() pass with valid data type
     *
     * @return void
     */
    public function testAssertStringCorrectType(): void
    {
        Validator::assertIsString(__FUNCTION__, 'string');
        // This assert won't be called if exception is thrown
        $this->assertTrue(true);
    }

    /**
     * Tests if assertString() throws exception when feed with invalid type argument.
     *
     * @return void
     */
    public function testAssertStringWrongType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Validator::assertIsString(__FUNCTION__, 666);
    }

    /**
     * Check if assertIntRange() main variable type is ensured to be integer.
     *
     * @return void
     */
    public function testAssertIntRangeVarType(): void
    {
        // ensure main variable is an integer
        $this->expectException(\InvalidArgumentException::class);
        Validator::assertIsIntRange(__FUNCTION__, 'string', 100, 200);
    }

    /**
     * Check if assertIntRange() range $min and $max are in right order.
     *
     * @return void
     */
    public function testAssertIntRangeMinMaxOrder(): void
    {
        // ensure main variable is an integer
        $this->expectException(\RuntimeException::class);
        Validator::assertIsIntRange(__FUNCTION__, 300, 500, 200);
    }

    /**
     * Check if assertIntRange() to ensure we check $var is in range nd $max bounds only
     *
     * @return void
     */
    public function testAssertIntRangeVarInMinMaxRange(): void
    {
        // ensure main variable is an integer
        $this->expectException(\InvalidArgumentException::class);
        Validator::assertIsIntRange(__FUNCTION__, 100, 300, 500);
    }

    /**
     * Tests assertType() helper.
     */
    public function testAssertType(): void
    {
        /**
         * Test data. Each entry is an array with a following keys:
         *   item    : value to be tested or array of values from which one value will be randomly picked for testing.
         *   types   : array of allowed `Validator::TYPE_xxx` types
         *   expected: @false if test is expected to fail (type of `item` is not in `types`), @true if it should pass.
         */
        $test_data = [
            [
                'item'     => false,
                'types'    => [Validator::TYPE_STRING],
                'expected' => false,
            ],
            [
                'item'     => false,
                'types'    => [Validator::TYPE_BOOL],
                'expected' => true,
            ],
            [
                'item'     => 'foo',
                'types'    => [Validator::TYPE_STRING],
                'expected' => true,
            ],
            [
                'item'     => 23,
                'types'    => [Validator::TYPE_STRING],
                'expected' => false,
            ],
            [
                'item'     => 666,
                'types'    => [Validator::TYPE_INTEGER],
                'expected' => true,
            ],
            [
                'item'     => 'fail',
                'types'    => [Validator::TYPE_INTEGER,
                               Validator::TYPE_BOOL],
                'expected' => false,
            ],

        ];

        foreach ($test_data as $key => $data) {
            $label = sprintf('test_data[%d]', $key);

            $test_passed = true;
            try {
                Validator::assertIsType($label, $data['item'], $data['types']);
            } catch (\Exception $ex) {
                $test_passed = false;
            }

            $msg = sprintf('Entry #%d: testing if "%s" (%s) is one of these: %s.',
                $key, $data['item'], gettype($data['item']), implode(', ', $data['types']));
            $this->assertEquals($test_passed, $data['expected'], $msg);
        }
    }
}
