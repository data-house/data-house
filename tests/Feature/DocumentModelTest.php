<?php

namespace Tests\Feature;

use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class DocumentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_url_is_signed(): void
    {
        config([
            'app.url' => 'http://localhost/',
            'app.internal_url' => 'http://localhost/',
        ]);

        $document = Document::factory()->create();

        $url = $document->internalUrl();

        $this->assertStringContainsString("documents/{$document->ulid}/internal-download", $url);
        $this->assertStringContainsString("expires=", $url);
        $this->assertStringContainsString("signature=", $url);
    }

    public function test_internal_uses_internal_app_url(): void
    {
        config([
            'app.url' => 'http://localhost:8000/',
            'app.internal_url' => 'http://docker.internal.host/',
        ]);

        $document = Document::factory()->create();

        $url = $document->internalUrl();

        $this->assertStringContainsString("docker.internal.host", $url);
        $this->assertStringContainsString("documents/{$document->ulid}/internal-download", $url);
        $this->assertStringContainsString("expires=", $url);
        $this->assertStringContainsString("signature=", $url);
    }
}
