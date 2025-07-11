<?php
declare(strict_types=1);

namespace MarcinOrlowski\ResponseBuilder\Tests\MessageManager;

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\MessageManager;
use MarcinOrlowski\ResponseBuilder\Tests\TestCase;
use Illuminate\Support\Facades\Lang;
use Mockery as m;

class MessageManagerTest extends TestCase
{
    /**
     * Tests that the correct success message is returned for a given API code.
     */
    public function testSuccessMessageResolution(): void
    {
        $apiCode = BaseApiCodes::OK();
        $expectedMessage = "This is a success message.";

        Lang::shouldReceive('get')
            ->with('response-builder::builder.ok', m::any())
            ->andReturn($expectedMessage);

        $message = MessageManager::get(true, $apiCode);
        $this->assertEquals($expectedMessage, $message);
    }

    /**
     * Tests that the correct error message is returned for a given API code.
     */
    public function testErrorMessageResolution(): void
    {
        $apiCode = BaseApiCodes::NO_ERROR_MESSAGE();
        $expectedMessage = "This is an error message.";

        Lang::shouldReceive('get')
            ->with('response-builder::builder.no_error_message', m::any())
            ->andReturn($expectedMessage);

        $message = MessageManager::get(false, $apiCode);
        $this->assertEquals($expectedMessage, $message);
    }

    /**
     * Tests that the default success message is returned when an unknown API code is provided.
     */
    public function testSuccessFallback(): void
    {
        $apiCode = 600; // An unknown code within valid range
        $expectedMessage = "Success fallback message.";

        Lang::shouldReceive('get')
            ->with('response-builder::builder.ok', m::any())
            ->andReturn($expectedMessage);

        $message = MessageManager::get(true, $apiCode);
        $this->assertEquals($expectedMessage, $message);
    }

    /**
     * Tests that the default error message is returned when an unknown API code is provided.
     */
    public function testErrorFallback(): void
    {
        $apiCode = 600; // An unknown code within valid range
        $expectedMessage = "Error fallback message.";

        Lang::shouldReceive('get')
            ->with('response-builder::builder.no_error_message', m::any())
            ->andReturn($expectedMessage);

        $message = MessageManager::get(false, $apiCode);
        $this->assertEquals($expectedMessage, $message);
    }

    /**
     * Tests that placeholders in the message are correctly replaced.
     */
    public function testPlaceholders(): void
    {
        $apiCode = BaseApiCodes::OK();
        $placeholders = ['value' => 'test'];
        $expectedMessage = "Success with value: test";

        Lang::shouldReceive('get')
            ->with('response-builder::builder.ok', array_merge($placeholders, ['api_code' => $apiCode]))
            ->andReturn($expectedMessage);

        $message = MessageManager::get(true, $apiCode, $placeholders);
        $this->assertEquals($expectedMessage, $message);
    }
}
