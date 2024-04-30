<?php

namespace Tests\Feature\Topics\Drivers;

use App\Topics\Facades\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Topics\InitializeTopics;
use Tests\TestCase;

class JsonConceptsDriverTest extends TestCase
{
    use InitializeTopics;

    protected $driverName = 'json-concepts';


    public function test_topics_returned_for_sub_topic_using_predefined_resource()
    {
        config(['topics.schemes' => 'area']);

        $this->createTopicFileWithContent([

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
            "id" => "area",
            "name" => "Area",
            "description" => null,
            "selected"  => collect([
                [
                    "id" => "energy",
                    "name" => "Energy",
                    "scheme" => "area",
                    "parent" => null,
                ],
            ]),
        ], $topics->first());
        
        $topics = Topic::from(["sustainable-mobility", 'energy-efficiency']);

        
        $this->assertEquals(1, $topics->count());
        
        $this->assertEquals([
            "id" => "area",
            "name" => "Area",
            "description" => null,
            "selected"  => collect([
                [
                    "id" => "energy",
                    "name" => "Energy",
                    "scheme" => "area",
                    "parent" => null,
                ],
                [
                    "id" => "mobility",
                    "name" => "Mobility",
                    "scheme" => "area",
                    "parent" => null,
                ]
            ]),
        ], $topics[0]);
        
    }

    public function test_topic_returned_when_reading_from_storage()
    {
        config(['topics.schemes' => 'area']);

        $this->createTopicFileWithContent([

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
            "id" => "area",
            "name" => "Area",
            "description" => null,
            "selected"  => collect([
                [
                    "id" => "energy",
                    "name" => "Energy",
                    "scheme" => "area",
                    "parent" => null,
                ],
            ]),
        ], $topics->first());
        
    }
    
}
