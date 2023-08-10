<?php

namespace Tests\Feature;

use App\Models\GeographicRegion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GeographicRegionTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_regions_can_be_loaded(): void
    {
        $regions = GeographicRegion::all();

        $this->assertCount(248, $regions);

        $this->assertEquals([
            "region-code" => 150,
            "region-name" => "Europe",
            "sub-region-code" => 155,
            "sub-region-name" => "Western Europe",
            "intermediate-region-code" => null,
            "intermediate-region-name" => null,
            "country-or-area" => "Germany",
            "M49-code" => 276,
            "iso-alpha2" => "DE",
            "iso-alpha3" => "DEU"
        ], $regions['DEU']);
        
        $this->assertFalse($regions['DE'] ?? false);
    }

    public function test_all_regions_returned_for_country()
    {
        $regions = GeographicRegion::from(collect(['DEU']));

        $this->assertEquals([
            "Europe",
            "Western Europe",
        ], $regions->flatten()->toArray());
        
        $regions = GeographicRegion::from(collect(['CRI']));

        $this->assertEquals([
            "Americas",
            "Latin America and the Caribbean",
            "Central America",
        ], $regions->flatten()->toArray());
        
    }
}
