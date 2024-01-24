<?php

namespace Tests\Feature;

use App\Data\TeamSettings;
use App\Data\UploadSettings;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Features;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateTeamSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_settings_handled(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team support is not enabled.');

            return;
        }

        $team = Team::factory()->create();

        $this->assertInstanceOf(TeamSettings::class, $team->settings);

        $this->assertNull($team->settings->upload);
    }
    
    public function test_upload_settings_handled(): void
    {
        if (! Features::hasTeamFeatures()) {
            $this->markTestSkipped('Team support is not enabled.');

            return;
        }

        $team = Team::factory()->create([
            'settings' => [
                'upload' => [
                    'uploadLinkUrl' => 'http://link.localhost',
                    'supportProjects' => true,
                ]
            ]
        ]);

        $this->assertInstanceOf(TeamSettings::class, $team->settings);
        
        $this->assertInstanceOf(UploadSettings::class, $team->settings->upload);

        $this->assertEquals('http://link.localhost', $team->settings->upload->uploadLinkUrl);
        $this->assertTrue($team->settings->upload->supportProjects);
    }
}
