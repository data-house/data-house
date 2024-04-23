<?php

namespace Tests\Feature\Topics;

use Illuminate\Support\Facades\Storage;

trait InitializeTopics
{
    protected function setUpInitializeTopics()
    {
        Storage::fake('local');


        $driver = property_exists($this, 'driverName') ? $this->driverName : config('topics.default');

        config([
            "topics.default" => $driver,
            "topics.drivers.{$driver}" => [
                'disk' => 'local',
                'file' => 'topics.json',
            ]
        ]);
    }

    protected function createTopicFileFrom(string $path)
    {
        Storage::disk('local')->put('topics.json', file_get_contents($path));
    }
    
    protected function createTopicFileWithContent(array $content)
    {
        Storage::disk('local')->put('topics.json', json_encode($content));
    }



}