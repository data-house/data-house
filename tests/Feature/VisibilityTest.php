<?php

namespace Tests\Feature;

use App\Models\Visibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_visibilities_for_documents_do_not_include_system(): void
    {
        $options = Visibility::forDocuments();

        $this->assertEquals([
            Visibility::PERSONAL,
            Visibility::TEAM,
            Visibility::PROTECTED,
            Visibility::PUBLIC,
        ], $options);
    }
}
