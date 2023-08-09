<?php

namespace Tests\Feature;

use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TopicTest extends TestCase
{
    use RefreshDatabase;

    public function test_topic_returned_for_sub_topic()
    {
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

    public function test_topics_returned_for_sub_topic()
    {
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
}
