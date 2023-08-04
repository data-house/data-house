<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\ProjectType;
use App\Models\User;
use App\Models\Visibility;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use PrinsFrank\Standards\Country\CountryAlpha3;

class InsertProject
{
    /**
     * Validate and create the given project.
     *
     * @param  array  $input
     */
    public function __invoke(array $input): Project
    {
        $validated = Validator::make($input, [
            'title' => ['required', 'string', 'min:1', 'max:255', 'unique:projects,title'],
            'type' => ['required', new Enum(ProjectType::class)],
            'topics' => ['nullable', 'array'],
            'topics.*' => ['string', 'min:1', 'max:255'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'max:3', new Enum(CountryAlpha3::class)],
            'organizations' => ['array:implementers,partners'],
            'organizations.implementers' => ['nullable', 'array'],
            'organizations.partners' => ['nullable', 'array'],
            'organizations.implementers.*' => ['nullable', 'string', 'min:1', 'max:255'],
            'organizations.partners.*' => ['nullable', 'string', 'min:1', 'max:255'],
            'properties' => ['nullable', 'array'],
            'slug' => ['nullable', 'string', 'min:1', 'max:255', 'unique:projects,slug'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ])->validate();

        return Project::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'topics' => $validated['topics'],
            'countries' => $validated['countries'],
            'organizations' => $validated['organizations'],
            'properties' => $validated['properties'] ?? [],
            'slug' => $validated['slug'] ?? str($validated['title'])->substr(0, 240)->slug()->toString(),
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

    }
}
