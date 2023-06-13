<?php

namespace App\DocumentConversion\Drivers;

use App\DocumentConversion\Contracts\Convertible;
use App\DocumentConversion\Contracts\Driver;
use App\DocumentConversion\ConvertedFile;
use App\DocumentConversion\Exceptions\ConversionException;
use App\DocumentConversion\Exceptions\UnsupportedConversionException;
use App\DocumentConversion\Format;
use GuzzleHttp\Psr7\MimeType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OnlyOfficeDriver implements Driver
{

    // https://api.onlyoffice.com/editors/conversionapi
    // https://www.onlyoffice.com/blog/2023/02/build-an-online-document-converter-with-onlyoffice-conversion-api

    private const CONVERSION_ENDPOINT = "/ConvertService.ashx";

    private array $config = [];


    private static $filetypeMap = [
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => 'docx',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    ];

    private static $errorCodes = [
        -1 => 'Unknown error.',
        -2 => 'Conversion timeout error.',
        -3 => 'Conversion error.',
        -4 => 'Error while downloading the document file to be converted.',
        -5 => 'Incorrect password.',
        -6 => 'Error while accessing the conversion result database.',
        -7 => 'Input error.',
        -8 => 'Invalid token.',
    ];


    public function __construct(array $config = null)
    {
        $this->config = [
            'disk' => $config['disk'],
            ...$config['drivers']['onlyoffice'] ?? ['url'=> null, 'jwt' => null]
        ];
    }

    public function convert(Convertible $model, Format $format): ConvertedFile
    {

        $request = $model->toConvertible();

        if(!array_key_exists($request->mimetype, static::supports())){
            throw new UnsupportedConversionException("Conversion from [{$request->mimetype}] to [{$format->value}] is not supported by the driver");
        }

        $acceptableConversionFormats = static::supports()[$request->mimetype];
        
        if(!in_array($format, $acceptableConversionFormats)){
            throw new UnsupportedConversionException("Conversion from [{$request->mimetype}] to [{$format->value}] is not supported by the driver");
        }


        try{

            $response = Http::acceptJson()
                ->asJson()
                ->post(rtrim($this->config['url'], '/') . self::CONVERSION_ENDPOINT, [
                    "async" => false,
                    "filetype" => static::$filetypeMap[$request->mimetype],
                    "key" => $request->key,
                    "outputtype" => $format->value,
                    "title" => $request->title,
                    "url" => $request->url
                ])
                ->throw();

            $json = $response->json();

            if($json['error'] ?? false){
                throw new ConversionException("Conversion ERROR " . $json['error'] . ' - ' . (static::$errorCodes[$json['error']] ?? 'unknown error'));
            }

            if(!$json['endConvert'] ?? false){
                throw new ConversionException("Conversion Not finished " . $response->body());
            }

            $conversionFileName = "{$request->key}.pdf";

            // assuming conversion disk uses local driver

            $sinkPath = Storage::disk($this->config['disk'])->path($conversionFileName);
            
            // if positive response download the converted file

            $fileDownloadResponse = Http::sink($sinkPath)
                ->get($json['fileUrl'])
                ->throw();


            return new ConvertedFile($this->config['disk'], $conversionFileName, MimeType::fromExtension($format->value));
            
        }
        catch(Throwable $ex)
        {
            logs()->error("Error converting document", ['request' => $request->toArray(), 'error' => $ex->getMessage()]);
            throw new ConversionException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }


    public static function supports(): array
    {
        return [
            MimeType::fromExtension('docx') => Format::cases(),
            MimeType::fromExtension('pptx') => Format::cases(),
            MimeType::fromExtension('pdf') => [
                Format::IMAGE,
            ],
        ];
    }

}