<?php

namespace App\DocumentThumbnail;

use Illuminate\Support\Arr;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Facades\Storage;
use \Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

class FileThumbnail
{
    /**
     * The cache copy of the file's hash name.
     *
     * @var string
     */
    protected $hashName = null;

    public function __construct(
        private string $disk,
        private string $path,
        private string $mime,   
    )
    {
        
    }

    /**
     * Store the converted file on a filesystem disk.
     *
     * @param  string  $path
     * @param  array|string  $options
     * @return string|false
     */
    public function store($path = '', $options = [])
    {
        $transferred = $this->storeAs($path, $name = $this->hashName(), $this->parseOptions($options));

        return $transferred ? $name : $transferred;
    }

    /**
     * Store the converted file on a filesystem disk.
     *
     * @param  string  $path
     * @param  string|array  $name
     * @param  array|string  $options
     * @return bool
     */
    public function storeAs($path, $name = null, $options = [])
    {
        if (is_null($name) || is_array($name)) {
            [$path, $name, $options] = ['', $path, $name ?? []];
        }

        $options = $this->parseOptions($options);

        $disk = Arr::pull($options, 'disk');

        return Container::getInstance()->make(FilesystemFactory::class)->disk($disk)->writeStream(
            implode('/', [$path, $name]), $this->readStream(), $options
        );
    }

    /**
     * Parse and format the given options.
     *
     * @param  array|string  $options
     * @return array
     */
    protected function parseOptions($options)
    {
        if (is_string($options)) {
            $options = ['disk' => $options];
        }

        return $options;
    }

    
    /**
     * Get a filename for the file.
     *
     * @param  string|null  $path
     * @return string
     */
    public function hashName($path = null)
    {
        if ($path) {
            $path = rtrim($path, '/').'/';
        }

        $hash = $this->hashName ?: $this->hashName = Str::random(40);

        if ($extension = $this->guessExtension()) {
            $extension = '.'.$extension;
        }

        return $path.$hash.$extension;
    }

    /**
     * The absolute path to the file, if the disk is local
     */
    public function absolutePath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    public function diskName(): string
    {
        return $this->disk;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function mimeType(): string
    {
        return $this->mime;
    }

    public function guessExtension(): ?string
    {
        if (!class_exists(MimeTypes::class)) {
            throw new \LogicException('You cannot guess the extension as the Mime component is not installed. Try running "composer require symfony/mime".');
        }

        return MimeTypes::getDefault()->getExtensions($this->mimeType())[0] ?? null;
    }

    /**
     * Read the content of the converted file as stream
     */
    public function readStream()
    {
        return Container::getInstance()->make(FilesystemFactory::class)->disk($this->disk)->readStream($this->path);
    }
}