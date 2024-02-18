<?php

namespace App\Data;

use App\Models\MimeType;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;
use Symfony\Component\Mime\MimeTypes;
use Spatie\LaravelData\Attributes\Computed;

class FileFormatData extends Data
{

    protected static $extensionFromatNameMap = [
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

        'ppt' => 'Slides',
        'odp' => 'Slides',
        'pptm' => 'Slides',
        'pptx' => 'Slides',

        'png' => 'Image',
        'jpe' => 'Image',
        'jpeg' => 'Image',
        'jpf' => 'Image',
        'jpg' => 'Image',
        'jpg2' => 'Image',

        'zip' => 'Compressed folder',
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

      $this->name = self::$extensionFromatNameMap[$this->extension] ?? '-';

      $this->icon = match ($this->mime) {
            MimeType::APPLICATION_PDF->value => 'codicon-file-pdf',
            default => 'codicon-file',
        };
    }

}
