<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\ImportSourceBrowser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ImportSourceBrowserTest extends TestCase
{
    /** @test */
    public function the_component_can_render()
    {
        $component = Livewire::test(ImportSourceBrowser::class);

        $component->assertStatus(200);
    }
}
