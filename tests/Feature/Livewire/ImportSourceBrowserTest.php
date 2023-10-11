<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ImportSourceBrowser;
use App\Models\Import;
use App\Models\ImportMap;
use App\Models\ImportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ImportSourceBrowserTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_component_can_render()
    {
        $fakeDisk = Storage::fake('webdav');

        Storage::shouldReceive('build')->with([
            'driver' => 'webdav',
            'url' => 'http://service',
            'user' => 'fake-disk-user',
            'password' => 'fake-disk-password',
        ])->andReturn($fakeDisk);

        $import = Import::factory()
            ->has(ImportMap::factory(), 'maps')
            ->create([
                'status' => ImportStatus::CREATED,
            ]);

        $component = Livewire::test(ImportSourceBrowser::class, ['import' => $import]);

        $component->assertStatus(200);
    }
}
