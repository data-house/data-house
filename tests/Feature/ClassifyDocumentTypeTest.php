<?php

namespace Tests\Feature;

use App\Actions\ClassifyDocumentType;
use App\Actions\SuggestDocumentAbstract;
use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class ClassifyDocumentTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_generic_type_returned_when_document_cannot_be_classified(): void
    {
        $document = Document::factory()->create([
            'title' => 'A generic title'
        ]);

        $action = new ClassifyDocumentType();

        $type = $action($document);

        $this->assertEquals(DocumentType::DOCUMENT, $type);
    }

    public function test_evaluation_report_type_returned(): void
    {
        $document = Document::factory()->create([
            'title' => 'P000_Evaluierungsbericht_20210820.pdf'
        ]);

        $action = new ClassifyDocumentType();

        $type = $action($document);

        $this->assertEquals(DocumentType::EVALUATION_REPORT, $type);
    }
    
}
