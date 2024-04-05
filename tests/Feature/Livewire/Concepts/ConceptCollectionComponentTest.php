<?php

namespace Tests\Feature\Livewire\Concepts;

use App\Livewire\Concepts\ConceptCollectionComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ConceptCollectionComponentTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ConceptCollectionComponent::class)
            ->assertStatus(200);
    }
}
