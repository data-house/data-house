<?php

namespace Tests\Feature;

use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_visibilities_for_documents_do_not_include_system_and_public(): void
    {
        $options = Visibility::forDocuments();

        $this->assertEquals([
            Visibility::PERSONAL,
            Visibility::TEAM,
            Visibility::PROTECTED,
        ], $options);
    }
    
    public function test_default_document_visibility(): void
    {
        config(['library.default_document_visibility' => 'protected']);

        $option = Visibility::defaultDocumentVisibility();

        $this->assertEquals(Visibility::PROTECTED, $option);
    }

    public function test_default_document_visibility_uses_fallback(): void
    {
        $option = Visibility::defaultDocumentVisibility();

        $this->assertEquals(Visibility::TEAM, $option);
    }
    
}
