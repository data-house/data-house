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

    public function test_topic_returned_for_sub_topic_using_predefined_resource()
    {
        Topic::clear();

        $topics = Topic::from(['energy efficiency']);

        $this->assertEquals(1, $topics->count());

        $this->assertEquals([
            "id" => "BI-5",
            "name" => "sustainable energy supply",
    
            "children"  => [
                [
                    "id" => "BI-5-1",
                    "name" => "renewable energies"
                ],
                [
                    "id" => "BI-5-2",
                    "name" => "energy efficiency"
                ]
            ],
            "selected"  => [
                [
                    "id" => "BI-5-2",
                    "name" => "energy efficiency"
                ]
            ],
        ], $topics->first());
        
        
    }

    public function test_topics_returned_for_sub_topic_using_predefined_resource()
    {
        Topic::clear();

        $topics = Topic::from(["renewable energies", 'energy efficiency']);
        
        $this->assertEquals(1, $topics->count());
        
        $this->assertEquals([
            "id" => "BI-5",
            "name" => "sustainable energy supply",
            
            "children"  => [
                [
                    "id" => "BI-5-1",
                    "name" => "renewable energies"
                ],
                [
                    "id" => "BI-5-2",
                    "name" => "energy efficiency"
                    ]
                ],
            "selected"  => [
                [
                    "id" => "BI-5-1",
                    "name" => "renewable energies"
                ],
                [
                    "id" => "BI-5-2",
                    "name" => "energy efficiency"
                    ]
            ],
        ], $topics->first());
        
        $topics = Topic::from(["sustainable mobility", 'energy efficiency']);

        
        $this->assertEquals(2, $topics->count());
        
        $this->assertEquals([
            "id" => "BI-5",
            "name" => "sustainable energy supply",
            
            "children"  => [
                [
                    "id" => "BI-5-1",
                    "name" => "renewable energies"
                ],
                [
                    "id" => "BI-5-2",
                    "name" => "energy efficiency"
                    ]
                ],
            "selected"  => [
                [
                    "id" => "BI-5-2",
                    "name" => "energy efficiency"
                    ]
            ],
        ], $topics[0]);
        
        $this->assertEquals([
            "id" => "BI-7",
            "name" => "sustainable mobility",
            
            "children"  => [
                [
                    "id" => "BI-7-0",
                    "name" => "sustainable mobility"
                ],
            ],
            "selected"  => [
                [
                    "id" => "BI-7-0",
                    "name" => "sustainable mobility"
                    ]
            ],
        ], $topics[1]);
        
    }

    public function test_topic_returned_when_reading_from_storage()
    {
        Topic::clear();
        
        Storage::fake('local');

        Storage::put('topics.json', json_encode([
            "Area" => [
                "id" => "area",
                "name" => "Area",
                "children" => [
                    [
                        "id" => "energy",
                        "name" => "Energy"
                    ]
                ]
            ],
        ]));


        config([
            'library.topics.file' => 'topics.json',
        ]);


        $topics = Topic::from(['Energy']);

        $this->assertEquals(1, $topics->count());

        $this->assertEquals([
            "id" => "area",
            "name" => "Area",
    
            "children"  => [
                [
                    "id" => "energy",
                    "name" => "Energy"
                ],
            ],
            "selected"  => [
                [
                    "id" => "energy",
                    "name" => "Energy"
                ]
            ],
        ], $topics->first());
        
        
    }
}
