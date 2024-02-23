<?php

namespace Tests\Feature\PdfProcessing;

use App\PdfProcessing\DocumentContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DocumentContentTest extends TestCase
{
    public function test_empty_document(): void
    {
        $content = new DocumentContent('');

        $this->assertTrue($content->isEmpty());
    }
    
    public function test_empty_paginated_document(): void
    {
        $content = new DocumentContent(['', '', '']);

        $this->assertTrue($content->isEmpty());
    }
}
