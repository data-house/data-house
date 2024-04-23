<?php

namespace App\Topics\Drivers;

use App\Topics\Contracts\Driver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Throwable;

class JsonDriver implements Driver
{
    private $data;

    public function __construct(
        private array $config = []
    )
    {
        $this->data = $this->getTopicFileContent(
            $this->config['file'],
            $this->config['disk'] ?? config('filesystems.default')
        );
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
        $childToParent = $this->data->mapWithKeys(function($t){
                return collect($t['children'] ?? [])->mapWithKeys(fn($child) => [$child['name'] => $t['name']]);
            })
            ->only($names);

        return $this->data->only($childToParent->values()->unique())
            ->map(function($t) use ($names){
                return [
                    ...$t,
                    'selected' => collect($t['children'] ?? [])->whereIn('name', $names)->values()->toArray(),
                ];
            })
            ->values();
    }

    
    /**
     * @inheritdoc
     */
    public function facets(): Collection
    {
        return $this->data->dump()
            ->mapWithKeys(function($entries, $schemeKey){

                $concepts = collect($entries['children'])
                    ->map(fn($t) => ['id' => $t['id'], 'name' => str($t['name'])->title()->toString()]);

                return [$schemeKey => $concepts->toArray()];
            });
    }

}