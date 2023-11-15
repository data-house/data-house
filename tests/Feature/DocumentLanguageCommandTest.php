<?php

namespace Tests\Feature;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PrinsFrank\Standards\Language\LanguageAlpha2;
use Tests\TestCase;

class DocumentLanguageCommandTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_recognized_languages_stored(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        Event::fake(); // This is required to prevent the Document pipeline to trigger upon document creation

        $document = Document::factory()
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
            ]);

        $this->artisan('document:language', [
            'documents' => [$document->ulid],
        ])
        ->assertSuccessful();

        $updatedDocument = $document->fresh();

        $this->assertNotNull($updatedDocument->languages);
        $this->assertEquals(collect(LanguageAlpha2::English), $updatedDocument->languages);
    }
    
    public function test_languages_not_overwritten_if_present(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');
        
        Event::fake(); // This is required to prevent the Document pipeline to trigger upon document creation
        
        $document = Document::factory()
            ->create([
                'disk_name' => 'local',
                'disk_path' => 'test.pdf',
                'languages' => collect(LanguageAlpha2::Italian)
            ]);

        $this->artisan('document:language', [
                'documents' => [$document->ulid],
            ])
            ->assertSuccessful();

        $updatedDocument = $document->fresh();

        $this->assertEquals(collect(LanguageAlpha2::Italian), $updatedDocument->languages);
    }
    
    public function test_only_supportes_files_are_processed(): void
    {
        Storage::fake();

        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');
        
        Event::fake(); // This is required to prevent the Document pipeline to trigger upon document creation

        $documents = Document::factory()
            ->count(2)
            ->state(new Sequence(
                ['mime' => 'text/plain'],
                [
                    'disk_name' => 'local',
                    'disk_path' => 'test.pdf',
                ]
            ))
            ->create();

        $this->artisan('document:language')
            ->assertSuccessful();

        $updatedDocument = $documents->last()->fresh();
        $firstDocument = $documents->first()->fresh();

        $this->assertEquals(collect(LanguageAlpha2::English), $updatedDocument->languages);
        $this->assertTrue($firstDocument->languages->isEmpty());
    }
}
