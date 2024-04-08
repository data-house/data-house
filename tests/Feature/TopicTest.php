<?php

namespace Tests\Feature;

use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TopicTest extends TestCase
{
    use RefreshDatabase;

    protected function setTopics(array $topics)
    {
        Topic::clear();

        Storage::fake('local');

        Storage::put('topics.json', json_encode($topics));

        config([
            'library.topics.file' => 'topics.json',
        ]);
    }
    
    protected function setTopicsFrom(string $filepath)
    {
        Topic::clear();

        Storage::fake('local');

        Storage::put('topics.json', file_get_contents($filepath));

        config([
            'library.topics.file' => 'topics.json',
        ]);
    }

    public function test_topics_returned_for_sub_topic_using_predefined_resource()
    {
        config(['library.topics.schemes' => 'area']);

        $this->setTopics([

            "concepts" => [

                "energy" => [
                    "name" => "Energy",
                    "scheme" => "area",
                    "parent" => null,
                ],
                "renewable-energy" => [
                    "name" => "Renewable Energy",
                    "scheme" => "area",
                    "parent" => "energy",
                ],
                "energy-efficiency" => [
                    "name" => "Energy Efficiency",
                    "scheme" => "area",
                    "parent" => "energy",
                ],
                "mobility" => [
                    "name" => "Mobility",
                    "scheme" => "area",
                    "parent" => null,
                ],
                "sustainable-mobility" => [
                    "name" => "Sustainable mobility",
                    "scheme" => "area",
                    "parent" => "mobility",
                ],
            ],

            "schemes" => [
                "area" => [
                    "name" => "Area",
                    "description" => null
                ],
            ],
        ]);

        $topics = Topic::from(["renewable-energy", 'energy-efficiency']);
        
        $this->assertEquals(1, $topics->count());
        
        $this->assertEquals([
            "id" => "energy",
            "name" => "Energy",
            "scheme" => "area",
            "parent" => null,
            "selected"  => collect([
                [
                    "name" => "Renewable Energy",
                    "scheme" => "area",
                    "parent" => "energy",
                ],
                [
                    "name" => "Energy Efficiency",
                    "scheme" => "area",
                    "parent" => "energy",
                ],
            ]),
        ], $topics->first());
        
        $topics = Topic::from(["sustainable-mobility", 'energy-efficiency']);

        
        $this->assertEquals(2, $topics->count());
        
        $this->assertEquals([
            "id" => "energy",
            "name" => "Energy",
            "scheme" => "area",
            "parent" => null,
            "selected"  => collect([
                [
                    "name" => "Energy Efficiency",
                    "scheme" => "area",
                    "parent" => "energy",
                ],
            ]),
        ], $topics[0]);
        
        $this->assertEquals([
            "id" => "mobility",
            "name" => "Mobility",
            "scheme" => "area",
            "parent" => null,
            "selected"  => collect([
                [
                    "name" => "Sustainable mobility",
                    "scheme" => "area",
                    "parent" => "mobility",
                ],
            ]),
        ], $topics[1]);
        
    }

    public function test_topic_returned_when_reading_from_storage()
    {
        config(['library.topics.schemes' => 'area']);

        $this->setTopics([

            "concepts" => [

                "energy" => [
                    "name" => "Energy",
                    "scheme" => "area",
                    "parent" => null,
                ],
                "renewable-energy" => [
                    "name" => "Renewable Energy",
                    "scheme" => "area",
                    "parent" => "energy",
                ],
            ],

            "schemes" => [
                "area" => [
                    "name" => "Area",
                    "description" => null
                ],
            ],
        ]);


        $topics = Topic::from(['renewable-energy']);

        $this->assertEquals(1, $topics->count());

        $this->assertEquals([
            "id" => "energy",
            "name" => "Energy",
            "scheme" => "area",
            "parent" => null,
            "selected"  => collect([
                [
                    "name" => "Renewable Energy",
                    "scheme" => "area",
                    "parent" => "energy",
                ]
            ]),
        ], $topics->first());
        
    }
    
}
