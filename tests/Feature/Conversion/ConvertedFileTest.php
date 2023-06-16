<?php

namespace Tests\Feature\Conversion;

use App\DocumentConversion\ConvertedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConvertedFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_converted_file_can_be_moved(): void
    {
        Storage::fake('local');

        Storage::putFileAs('input', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $convertedFile = new ConvertedFile('local', 'input/test.pdf', 'application/pdf');

        $path = $convertedFile->store('output', 'local');

        $this->assertTrue(is_string($path));

        Storage::assertExists("output/{$path}");
    }

    public function test_converted_file_can_be_moved_with_name(): void
    {
        Storage::fake('local');

        Storage::putFileAs('input', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $convertedFile = new ConvertedFile('local', 'input/test.pdf', 'application/pdf');

        $path = $convertedFile->storeAs('output', 'variant.pdf', 'local');

        $this->assertTrue($path);

        Storage::assertExists('output/variant.pdf');
    }

    public function test_file_details_returned(): void
    {
        Storage::fake('local');

        Storage::putFileAs('input', new File(base_path('tests/fixtures/documents/data-house-test-doc.pdf')), 'test.pdf');

        $convertedFile = new ConvertedFile('local', 'input/test.pdf', 'application/pdf');

        $this->assertEquals('input/test.pdf', $convertedFile->path());
        $this->assertEquals('local', $convertedFile->diskName());
        $this->assertEquals('application/pdf', $convertedFile->mimeType());
        $this->assertEquals('pdf', $convertedFile->guessExtension());

        $this->assertNotNull($convertedFile->absolutePath());

        $this->assertStringStartsWith('folder/', $convertedFile->hashName('folder'));
        $this->assertStringEndsWith('pdf', $convertedFile->hashName('folder'));

        // Testing that the destructor clears the used resources

        $convertedFile = null;

        Storage::assertMissing('input/test.pdf');
    }
}
