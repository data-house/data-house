<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use \Illuminate\Support\Str;

class ImportSourceBrowser extends Component
{

    /**
     * @var \App\Models\Import
     */
    public $import;

    public $path;
    
    public $location;

    public $selectedPaths;
    
    public $selection;

    public $directories = null;
    
    public $files = null;

    public function mount($import)
    {
        $this->import = $import;
        $this->path = [];
        $this->selectedPaths = [];
        $this->selection = null;
    }

    public function navigate($path)
    {
        $this->path[] = $path;
    }
    
    public function select($path)
    {
        $key = str($path)->slug()->value();

        if(isset($this->selectedPaths[$key])){
            unset($this->selectedPaths[$key]);

            return;
        }

        $this->selectedPaths[$key] = $path;
    }
    
    public function navigateUp()
    {
        array_pop($this->path);
    }

    public function render()
    {

        $this->location = collect(rtrim(parse_url($this->import->configuration['url'], PHP_URL_HOST), '/'))->merge($this->path)->join(' / ');
        
        $compoundPath = collect($this->path)->join('/');

        $storagePathPrefix = ltrim(parse_url($this->import->configuration['url'], PHP_URL_PATH), '/') . $compoundPath;

        $disk = Storage::build([
            'driver' => $this->import->source->value,
            ...$this->import->configuration,
        ]);


        $this->directories = collect($disk->directories($compoundPath))->mapWithKeys(function($file) use ($storagePathPrefix) {
            $basename = basename($file, $storagePathPrefix);
            return [$file => $basename];
        })->toArray();
        $this->files = collect($disk->files($compoundPath))->mapWithKeys(function($file) use ($storagePathPrefix) {
            $basename = basename($file, $storagePathPrefix);
            return [$file => $basename];
        })->toArray();


        return view('livewire.import-source-browser');
    }
}
