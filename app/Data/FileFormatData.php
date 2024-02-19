<?php

namespace App\Data;

use App\Models\MimeType;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;
use Symfony\Component\Mime\MimeTypes;
use Spatie\LaravelData\Attributes\Computed;

class FileFormatData extends Data
{

    protected static $extensionToFormatName = [
        'pdf' => 'PDF',
        
        'doc' => 'Word',
        'odt' => 'Word',
        'docm' => 'Word',
        'docx' => 'Word',

        'ods' => 'Spreadsheet',
        'xls' => 'Spreadsheet',
        'xlsb' => 'Spreadsheet',
        'xlsm' => 'Spreadsheet',
        'xlsx' => 'Spreadsheet',

        'ppt' => 'Slideshow',
        'odp' => 'Slideshow',
        'pptm' => 'Slideshow',
        'pptx' => 'Slideshow',

        'png' => 'Image',
        'jpe' => 'Image',
        'jpeg' => 'Image',
        'jpf' => 'Image',
        'jpg' => 'Image',
        'jpg2' => 'Image',

        'zip' => 'Compressed folder',
    ];
    
    protected static $extensionToIcon = [
        'pdf' => 'codicon-file-pdf',
        
        'doc' => 'codicon-file',
        'odt' => 'codicon-file',
        'docm' => 'codicon-file',
        'docx' => 'codicon-file',

        'ods' => 'codicon-table',
        'xls' => 'codicon-table',
        'xlsb' => 'codicon-table',
        'xlsm' => 'codicon-table',
        'xlsx' => 'codicon-table',

        'ppt' => 'codicon-symbol-constant',
        'odp' => 'codicon-symbol-constant',
        'pptm' => 'codicon-symbol-constant',
        'pptx' => 'codicon-symbol-constant',

        'png' => 'codicon-file-media',
        'jpe' => 'codicon-file-media',
        'jpeg' => 'codicon-file-media',
        'jpf' => 'codicon-file-media',
        'jpg' => 'codicon-file-media',
        'jpg2' => 'codicon-file-media',

        'zip' => 'codicon-file-zip',
    ];


    #[Computed]
    public string $icon;
    
    #[Computed]
    public string $name;
    
    #[Computed]
    public string $extension;


    public function __construct(
      public string $mime,
    ) {

      $mimeTypes = MimeTypes::getDefault();

      $this->extension = Arr::first($mimeTypes->getExtensions($this->mime));

      $this->name = self::$extensionToFormatName[$this->extension] ?? '-';

      $this->icon = self::$extensionToIcon[$this->extension] ?? 'codicon-file';
    }

}
