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
        return $this->concepts->only($names)
            ->whereNotNull('parent')
            ->groupBy(['parent'])
            ->map(function($t, $keys) use ($names){
                
                return [
                    ...$this->concepts->get($keys),
                    ...['id' => $keys],
                    'selected' => collect($t),
                ];
            })
            ->values();
    }


    
    /**
     * Get the topics to be used in filters/facets
     */
    public function facets(): Collection
    {
        return $this->concepts->whereNull('parent')->groupBy('scheme')
            ->filter()->mapWithKeys(function($entries, $schemeKey){

                $name = $this->schemes[$schemeKey]['name'] ?? $schemeKey;

                return [$name => $entries];
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