<?php

namespace App\DocumentThumbnail\Drivers;

use App\DocumentThumbnail\Contracts\Driver;
use App\DocumentThumbnail\FileThumbnail;
use App\DocumentThumbnail\Exceptions\ConversionException;
use App\DocumentThumbnail\Exceptions\UnsupportedConversionException;
use App\PdfProcessing\DocumentReference;
use Carbon\Carbon;
use GuzzleHttp\Psr7\MimeType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ImaginaryDriver implements Driver
{

    // https://github.com/h2non/imaginary
    // https://hub.docker.com/r/nextcloud/aio-imaginary
    // https://github.com/nextcloud/all-in-one/blob/main/Containers/imaginary/Dockerfile

    private const CONVERSION_ENDPOINT = "/pipeline";

    private array $config = [];


    // private static $errorCodes = [
    //     -1 => 'Unknown error.',
    //     -2 => 'Conversion timeout error.',
    //     -3 => 'Conversion error.',
    //     -4 => 'Error while downloading the document file to be converted.',
    //     -5 => 'Incorrect password.',
    //     -6 => 'Error while accessing the conversion result database.',
    //     -7 => 'Input error.',
    //     -8 => 'Invalid token.',

    // //     ErrNotFound             = NewError("Not found", http.StatusNotFound)
	// // ErrInvalidAPIKey        = NewError("Invalid or missing API key", http.StatusUnauthorized)
	// // ErrMethodNotAllowed     = NewError("HTTP method not allowed. Try with a POST or GET method (-enable-url-source flag must be defined)", http.StatusMethodNotAllowed)
	// // ErrGetMethodNotAllowed  = NewError("GET method not allowed. Make sure remote URL source is enabled by using the flag: -enable-url-source", http.StatusMethodNotAllowed)
	// // ErrUnsupportedMedia     = NewError("Unsupported media type", http.StatusNotAcceptable)
	// // ErrOutputFormat         = NewError("Unsupported output image format", http.StatusBadRequest)
	// // ErrEmptyBody            = NewError("Empty or unreadable image", http.StatusBadRequest)
	// // ErrMissingParamFile     = NewError("Missing required param: file", http.StatusBadRequest)
	// // ErrInvalidFilePath      = NewError("Invalid file path", http.StatusBadRequest)
	// // ErrInvalidImageURL      = NewError("Invalid image URL", http.StatusBadRequest)
	// // ErrMissingImageSource   = NewError("Cannot process the image due to missing or invalid params", http.StatusBadRequest)
	// // ErrNotImplemented       = NewError("Not implemented endpoint", http.StatusNotImplemented)
	// // ErrInvalidURLSignature  = NewError("Invalid URL signature", http.StatusBadRequest)
	// // ErrURLSignatureMismatch = NewError("URL signature mismatch", http.StatusForbidden)
	// // ErrResolutionTooBig     = NewError("Image resolution is too big", http.StatusUnprocessableEntity)
    // ];


    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function thumbnail(DocumentReference $request): FileThumbnail
    {

        if(! collect(static::supports())->contains($request->mimeType)){
            throw new UnsupportedConversionException("Thumbnail from [{$request->mimeType}] is not supported by the driver");
        }


        $operations = collect([
				$this->requireConversionOperation($request->mimeType) ? [
					'operation' => 'convert',
					'params' => [
						'type' => 'jpeg',
					]
				] : null,
                [
					'operation' => 'autorotate',
				],
				[
					'operation' => 'smartcrop',
					'params' => [
                        'quality' => 90,
						'width' => 960,
						'height' => 600,
						'type' => 'jpeg',
						'norotation' => 'true',
					]
				]
			])->filter();


        try{
            
            $diskName = $this->config['disk'] ?? config('filesystems.default');
            
            $disk = Storage::disk($diskName);

            $conversionFileName = str('thb_')->append(Str::ulid(), '.jpg')->toString();

            $sinkPath = $disk->path($conversionFileName);

            $response = Http::acceptJson()
                ->timeout(Carbon::SECONDS_PER_MINUTE * 2)
                ->sink($sinkPath)
                // Use POST to upload directly, like done in Nextcloud https://github.com/nextcloud/server/blob/112d516f27a0cb752e464d103dda0ba324983e3e/lib/private/Preview/Imaginary.php#L172
                ->get(rtrim($this->config['url'], '/') . self::CONVERSION_ENDPOINT, [
                    'operations' => $operations->toJson(),
                    'url' => $request->url,
					'nextcloud' => ['allow_local_address' => true],
                ])
                ->throw();

            if($disk->size($conversionFileName) != $response->header('content-length')){
                logs()->error("Error converting document. Downloaded file size not corresponding to response size", ['request' => $request, 'error' => $response->headers()]);
                throw new ConversionException('Not equal file size after download', 500);
            }

            return new FileThumbnail($diskName, $conversionFileName, MimeType::fromExtension('jpg'));
            
        }
        catch(Throwable $ex)
        {
            logs()->error("Error converting document", ['request' => $request, 'error' => $ex->getMessage()]);
            throw new ConversionException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }


    public static function supports(): array
    {
        return [
            MimeType::fromExtension('png'),
            MimeType::fromExtension('jpg'),
            MimeType::fromExtension('pdf'),
        ];
    }
    
    protected function requireConversionOperation($mimeType): bool
    {
        switch ($mimeType) {
			case 'image/svg+xml':
			case 'application/pdf':
			case 'application/illustrator':
				return true;
				break;
			default:
				return false;
		}
    }

}