<?php

namespace App\Console\Commands;

use App\Actions\Project\InsertProject;
use App\Models\Document;
use App\Models\ProjectStatus;
use App\Models\ProjectType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use PrinsFrank\Standards\Country\CountryAlpha3;
use \Illuminate\Support\Str;
use Throwable;

class ProjectImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:import {file} {--disk=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import project data from file';

    /**
     * Execute the console command.
     */
    public function handle(InsertProject $insert)
    {
        $file = $this->argument('file');

        $disk = $this->option('disk') ?? config('filesystems.default');

        if(!Storage::disk($disk)->exists($file)){
            throw new InvalidArgumentException("Specified file [{$file}] does not exist in disk [{$disk}].");
        }
        
        if(!Str::endsWith($file, 'json')){
            throw new InvalidArgumentException("Expecting a JSON file, given [{$file}]");
        }
        
        $content = Storage::disk($disk)->json($file);
        
        if($content === false){
            throw new InvalidArgumentException("The content of the [{$file}] might have invalid structure.");
        }

        collect($content)
            ->each(function($p) use ($insert) {
                try {
                    $project = $insert([
                        'title' => $p['title']['de'] ?? $p['title']['en'] ?? $p['title'],
                        'slug' => $p['slug'],
                        'topics' => $p['topics'],
                        'type' => $p['type'] ? ProjectType::from($p['type']) : null,
                        'countries' => $p['countries'],
                        'organizations' => $p['organizations'],
                        'properties' => [
                            ...$p['properties'] ?? [],
                            'title_en' => $p['title']['en'] ?? null
                        ],
                        'description' => $p['description'],
                        'starts_at' => $p['starts_at'],
                        'ends_at' => $p['ends_at'],
                        'status' => $p['status'] ? ProjectStatus::from($p['status']) : null,
                        'iki-funding' => $p['iki-funding'] ?? null,
                        'website' => $p['website'] ?? null,
                    ]);

                    $documents = Collection::wrap($p['documents'] ?? []);

                    $documents->each(function($doc) use ($project){
                        try {
                            $d = Document::whereTitle($doc)->firstOrFail();
                            $d->project()->associate($project);
                            $d->save();
                        } catch (Throwable $th) {
                            $this->error("Document [{$doc}] not found [project = {$project->slug}]");
                        }

                    });

                } catch (Throwable $th) {
                    $this->error("Failed to add project [{$p['slug']}]: {$th->getMessage()}]");
                }

            });
    }
}
