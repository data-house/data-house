<?php

namespace Tests\Feature\Livewire\Concepts;

use App\Livewire\Concepts\ConceptComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ConceptComponentTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ConceptComponent::class)
            ->assertStatus(200);
    }
}
