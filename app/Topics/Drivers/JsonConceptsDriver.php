<?php

namespace App\Topics\Drivers;

use App\Topics\Contracts\Driver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

class JsonConceptsDriver implements Driver
{
    private $concepts;
    
    private $schemes;

    public function __construct(
        private array $config = []
    )
    {
        $data = $this->getTopicFileContent(
            $this->config['file'],
            $this->config['disk'] ?? config('filesystems.default')
        );

        $availableConcepts = collect($data->get('concepts'));

        $enabledSchemes = self::enabledSchemes();

        $this->schemes = collect($data->get('schemes'));

        $this->concepts = $availableConcepts->whereIn('scheme', $enabledSchemes);
    }



    protected function getTopicFileContent(string $path, string $disk): Collection
    {
        return Storage::disk($disk)->exists($path) ? collect(Storage::disk($disk)->json($path)) : collect();
    }
    
    public function enabledSchemes(): Collection
    {
        return str($this->config['schemes'])->explode(',')->filter()->values();
    }
    
    public function concepts(): Collection
    {
        return $this->concepts;
    }
    
    public function schemes(): Collection
    {
        return $this->schemes;
    }

    /**
     * Select the topics from the hierarchy based on the applied ones
     */
    public function from(array|Collection $names): Collection
    {
        $topicsOfInterest = $this->concepts->only($names)->pluck('parent')->values()->filter();

        return $this->concepts->only($topicsOfInterest)
            ->whereNull('parent')
            ->groupBy('scheme', true)
            ->map(function($t, $keys) use ($names){
                return [
                    ...$this->schemes->get($keys),
                    ...['id' => $keys],
                    'selected' => collect($t)->map(function($entry, $entryKey){
                        return [
                            'id' => $entryKey,
                            ...$entry,
                        ];
                    })->values(),
                ];
            })
            ->values();
    }


    
    /**
     * Get the topics to be used in filters/facets
     */
    public function facets(): Collection
    {
        return $this->concepts->whereNull('parent')->groupBy('scheme', true)
            ->filter()->mapWithKeys(function($entries, $schemeKey){

                $withIds = $entries->map(function($entry, $entryKey){
                    return [
                        ...$entry,
                        'id' => $entryKey,
                    ];
                });

                $name = $this->schemes[$schemeKey]['name'] ?? $schemeKey;

                return [$name => $withIds->values()];
            });
    }

    public function nameFromKey(string $key): string
    {
        $topic = $this->concepts->get($key);

        if(!$topic){
            throw new InvalidArgumentException("No topic with key [{$key}]");
        }

        return $topic['name'] ?? $topic['id'];
    }
    
}