<?php

namespace Tests\Feature\PdfProcessing;

use Tests\TestCase;
use App\PdfProcessing\DocumentContent;

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
