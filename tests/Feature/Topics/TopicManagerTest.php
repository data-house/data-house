<?php

namespace Tests\Feature\Topics;

use App\Topics\Drivers\JsonDriver;
use App\Topics\Facades\Topic;
use App\Topics\TopicManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TopicManagerTest extends TestCase
{
    public function test_default_driver_returned(): void
    {
        config(['topics.default' => 'a-driver']);

        $driver = app()->make(TopicManager::class)->getDefaultDriver();

        $this->assertEquals('a-driver', $driver);
    }
    
    public function test_json_driver_created(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('topics.json', "[]");

        config(['topics.drivers.json' => [
            'disk' => 'local',
            'file' => 'topics.json',
        ]]);

        $driver = Topic::driver('json');

        $this->assertInstanceOf(JsonDriver::class, $driver);
    }
}
