<?php

namespace Tests\Feature\Livewire\Concepts;

use App\Livewire\Concepts\ConceptCollectionListingComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ConceptCollectionListingComponentTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ConceptCollectionListingComponent::class)
            ->assertStatus(200);
    }
}
