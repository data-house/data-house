<?php

namespace Tests\Feature\Conversion;

use App\DocumentConversion\ConvertedFile;
use App\DocumentConversion\Drivers\OnlyOfficeDriver;
use App\DocumentConversion\Format;
use App\Models\Disk;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OnlyOfficeDriverTest extends TestCase
{

    use RefreshDatabase;

    public function test_docx_conversion_to_pdf(): void
    {
        Storage::fake('local');

        config(['app.internal_url' => 'http://app.internal.url/']);

        Http::preventStrayRequests();

        Http::fake([
            'http://onlyoffice/ConvertService.ashx' => Http::response('{"fileUrl":"http://onlyoffice/cache/files/data/conv_Khirz6zTPdfd7_pdf/output.pdf/Test.pdf?md5=4Z0-gvDsahr4_szYkAmhcA&expires=1686560668&filename=Test.pdf","fileType":"pdf","percent":100,"endConvert":true}', 200),
            'http://onlyoffice/cache/*' => Http::response('Hello World', 200),
            
        ]);
        
        Storage::putFileAs('', new File(base_path('tests/fixtures/documents/data-house-test-doc.docx')), 'test.docx');

        $document = Document::factory()->create([
            'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'disk_name' => 'local',
            'disk_path' => 'test.docx',
        ]);

        $driver = new OnlyOfficeDriver([
            'disk' => 'local',
            'drivers' => [
                'onlyoffice' => [
                    'url' => 'http://onlyoffice',
                    'jwt' => null,
                ]
            ]
        ]);

        /**
         * @var \App\DocumentConversion\ConvertedFile
         */
        $convertedFile = $driver->convert($document, Format::PDF);

        $this->assertStringStartsWith('http://app.internal.url', $document->internalUrl());

        $this->assertNotNull($convertedFile);
        $this->assertInstanceOf(ConvertedFile::class, $convertedFile);

        Http::assertSentCount(2);

        Http::assertSent(function (Request $request) use ($document) {

            $data = $request->data();

            return $request->url() == 'http://onlyoffice/ConvertService.ashx' &&
                   $request->isJson() &&
                   $data['async'] === false &&
                   $data['filetype'] === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' &&
                   $data['key'] == $document->getKey() &&
                   $data['outputtype'] === 'pdf' &&
                   $data['title'] === $document->title &&
                   $data['url'] === $document->internalUrl();
        });

        $this->assertFileExists($convertedFile->absolutePath());
    }
}
