<?php

namespace App\Topics\Drivers;

use App\Topics\Contracts\Driver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Throwable;

class NullDriver implements Driver
{
    private $data;

    public function __construct(
        private array $config = []
    )
    {
        $this->data = collect();
    }

    protected function getTopicFileContent(string $path, string $disk): Collection
    {
        return Storage::disk($disk)->exists($path) ? collect(Storage::disk($disk)->json($path)) : collect();
    }
    
    /**
     * @inheritdoc
     */
    public function concepts(): Collection
    {
        return $this->data;
    }
    
    /**
     * @inheritdoc
     */
    public function schemes(): Collection
    {
        return $this->data->keys();
    }

    /**
     * @inheritdoc
     */
    public function from(array|Collection $names): Collection
    {
        return collect();
    }

    
    /**
     * @inheritdoc
     */
    public function facets(): Collection
    {
        return collect();
    }

}