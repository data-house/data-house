<?php

namespace App\Actions\Catalog;

use App\Models\User;
use App\Models\Catalog;
use App\Models\Visibility;
use Illuminate\Validation\Rule;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;

class CreateCatalog
{
    /**
     * Creates a catalog
     */
    public function __invoke(string $title, ?string $description = null, Visibility $visibility = Visibility::PERSONAL, ?User $user = null): Catalog
    {
        /**
         * @var \App\Models\User
         */
        $user = $user ?? auth()->user();

        throw_unless($user->can('create', Catalog::class), AuthorizationException::class);

        $input = [
            'title' => $title,
            'description' => $description,
        ];

        Validator::make($input, [
            'title' => ['required',
                'string',
                'min:1',
                'max:255',
                Rule::unique((new Catalog)->getTable(), 'title')
                    ->where('visibility', Visibility::TEAM)
                    ->where('team_id', $user->currentTeam->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:6000'],
        ])->validate();


        return Catalog::forceCreate([
                'title' => $title,
                'description' => $description,
                'visibility' => $visibility ?? Visibility::PERSONAL,
                'user_id' => $user->getKey(),
                'team_id' => $user->currentTeam->getKey(),
            ]);
    }
}
