<?php

namespace App\PdfProcessing;

use Carbon\Carbon;
use JsonSerializable;

class DocumentReference implements JsonSerializable
{

    /**
     * The mime type of the referenced document
     */
    public readonly string $mimeType;
    
    /**
     * The absolute path to the file location, if on a local disk
     */
    public readonly string $path;
    
    /**
     * The URL from which the document content can be downloaded, if on a remote disk
     */
    public readonly string $url;

    public function __construct(
        string $mimeType
        )
    {
        $this->mimeType = $mimeType;
    }


    public function path(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Check if the reference points to a path in a local filesystem
     */
    public function isLocal(): bool
    {
        return filled($this->path ?? null);
    }
    
    /**
     * Check if the reference points to a remote url
     */
    public function isRemote(): bool
    {
        return filled($this->url ?? null);
    }


    /**
     * Get the JSON representation of the object.
     *
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): mixed
    {
        return [
            'mime_type' => $this->mimeType,
            'path' => $this->path,
            'url' => $this->url,
        ];
    }


    public static function build(string $mimeType)
    {
        return new self($mimeType);
    }

}
